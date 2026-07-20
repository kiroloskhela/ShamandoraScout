<?php

namespace App\Http\Controllers;

use App\Support\ManualPrimaryKey;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MarhalaEntryQuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $qetaat = DB::table('Qetaa')->get();
        $entryQuestions = DB::table('MarhalaEntryQuestions')
            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
            ->Join('QuestionsTypes', 'MarhalaEntryQuestions.RequiredAnswerType', '=', 'QuestionsTypes.QuestionType')
            ->select('MarhalaEntryQuestions.*', 'Qetaa.QetaaName', 'QuestionsTypes.QuestionTypeInArabicWords')
            ->get();

        return view('entry-questions.entry-questions-index', ['entryQuestions' => $entryQuestions, 'title' => __('Questions')]);
    }

    public function create()
    {
        $qetaat = DB::table('Qetaa')->get();
        $questionTypes = DB::table('QuestionsTypes')->get();

        return view('entry-questions.entry-questions-create', ['qetaat' => $qetaat, 'questionTypes' => $questionTypes]);
    }

    public function insert(Request $request)
    {
        $num = (int) $request->input('memberA', 0);
        if ($num > 6) {
            $num = 6;
        }

        $choices = [];
        for ($i = 1; $i <= $num; $i++) {
            $val = trim((string) $request->input('choice'.$i, ''));
            if ($val !== '') { // ✅ تجاهل الفاضي
                $choices[] = $val;
            }
        }

        // Validation: لو السؤال MultipleChoice و مفيش ولا اختيار => رجّع Error
        if ($request->required_answer_type === 'MultipleChoice' && empty($choices)) {
            return back()->withErrors(['choices' => __('At least one choice is required')])->withInput();
        }

        $stringOfChoices = implode('|', $choices);
        $isRequired = $request->has('questionIsRequired') ? 1 : 0;

        // Prod PK is NOT AUTO_INCREMENT — allocate explicitly (see scripts/check-auto-increment.php).
        $thisQuestionID = ManualPrimaryKey::next('MarhalaEntryQuestions', 'QuestionID');

        DB::table('MarhalaEntryQuestions')->insert([
            'QuestionID' => $thisQuestionID,
            'QetaaID' => $request->qetaa_id,
            'QuestionText' => $request->question_text,
            'RequiredAnswerType' => $request->required_answer_type,
            'MCAnswer' => $stringOfChoices,
            'NotToBeShown' => 0,
            'IsRequired' => $isRequired,
        ]);

        return redirect()->route('entry-questions.index')
            ->with('status', __('Question added successfully: ').$thisQuestionID);
    }

    public function edit($id)
    {
        $qetaat = DB::table('Qetaa')->get();
        $qetaaSelected = DB::table('MarhalaEntryQuestions')
            ->where('QuestionID', '=', $id)
            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
            ->select('Qetaa.QetaaID', 'Qetaa.QetaaName')
            ->first();
        $questionTypes = DB::table('QuestionsTypes')->get();
        // $entryQuestions = DB::table('MarhalaEntryQuestions')->where('QuestionID', $id)->first();
        $entryQuestion = DB::table('MarhalaEntryQuestions')
            ->where('QuestionID', $id)
            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
            ->Join('QuestionsTypes', 'MarhalaEntryQuestions.RequiredAnswerType', '=', 'QuestionsTypes.QuestionType')
            ->select('MarhalaEntryQuestions.QuestionID',
                'MarhalaEntryQuestions.QuestionText',
                'Qetaa.QetaaName',
                'QuestionsTypes.QuestionTypeInArabicWords',
                'MarhalaEntryQuestions.RequiredAnswerType',
                'MarhalaEntryQuestions.MCAnswer',
                'MarhalaEntryQuestions.NotToBeShown',
                'MarhalaEntryQuestions.IsRequired')
            ->first();
        $arrayOfMCAnswers = explode('|', $entryQuestion->MCAnswer);

        // return $arrayOfMCAnswers;
        return view('entry-questions.entry-questions-edit', ['entryQuestion' => $entryQuestion, 'qetaat' => $qetaat, 'questionTypes' => $questionTypes, 'qetaaSelected' => $qetaaSelected, 'arrayOfMCAnswers' => $arrayOfMCAnswers]);
    }

    public function updates(Request $request, $id)
    {

        if ($request->has('questionNotToBeShown')) {
            $notToBeShown = 1;
        } else {
            $notToBeShown = 0;
        }

        if ($request->has('questionIsRequired')) {
            $isRequired = 1;
        } else {
            $isRequired = 0;
        }

        $numberOfChoices = $request->answers;

        $stringOfChoices = '';
        for ($i = 1; $i <= $numberOfChoices; $i++) {
            $answer = 'answer'.$i;
            $stringOfChoices = $stringOfChoices.$request->$answer;

            if ($i < $numberOfChoices) {
                $stringOfChoices = $stringOfChoices.'|';
            }
        }

        DB::table('MarhalaEntryQuestions')
            ->where('QuestionID', $id)
            ->update(['QuestionText' => $request->question_text,
                'QetaaID' => $request->qetaa_id,
                'NotToBeShown' => $notToBeShown,
                'MCAnswer' => $stringOfChoices,
                'IsRequired' => $isRequired,
            ]);

        return redirect()->route('entry-questions.index')->with('status', __('Question updated successfully'));

    }

    public function deletes($id)
    {
        $entryQuestions = DB::table('MarhalaEntryQuestions')
            ->join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
            ->join('QuestionsTypes', 'MarhalaEntryQuestions.RequiredAnswerType', '=', 'QuestionsTypes.QuestionType')
            ->select(
                'MarhalaEntryQuestions.*',
                'Qetaa.QetaaName',
                'QuestionsTypes.QuestionTypeInArabicWords'
            )
            ->where('QuestionID', $id)
            ->first();

        return view('entry-questions.entry-questions-delete', [
            'entryQuestions' => $entryQuestions,
            'title' => __('Delete question'),
        ]);
    }

    public function destroy($id)
    {
        $deleted = DB::table('MarhalaEntryQuestions')->where('QuestionID', $id)->delete();

        return redirect()->route('entry-questions.index')->with('status', __('Question cancelled successfully'));
    }
}
