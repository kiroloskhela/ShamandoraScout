<?php

namespace App\Http\Controllers;

use App\Http\Requests\LookupStoreRequest;
use App\Http\Requests\LookupUpdateRequest;
use App\Support\ManualPrimaryKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventTypeController extends LookupTableController
{
    protected string $lookupKey = 'event-type';

    public function index()
    {
        $config = $this->lookupConfig();
        $records = DB::table($config['table'])->get()->map(function ($row) {
            $row->TakesReservationLabel = ! empty($row->TakesReservation) ? __('Yes') : __('No');

            return $row;
        });

        return view($config['views']['index'], [
            $config['variables']['index'] => $records,
        ]);
    }

    public function create()
    {
        return view('event-type.create');
    }

    public function insert(LookupStoreRequest $request)
    {
        $request->validate([
            'event_type_name' => ['required', 'string', 'max:255'],
            'takes_reservation' => ['nullable', 'boolean'],
        ]);

        $config = $this->lookupConfig();
        $payload = $this->payload($config, $request, true);
        $payload[$config['primary_key']] = ManualPrimaryKey::next($config['table'], $config['primary_key']);

        DB::table($config['table'])->insert($payload);

        return $this->redirectWithMessage($config, 'store', $request);
    }

    public function edit($id)
    {
        $config = $this->lookupConfig();
        $record = $this->findRecord($config, $id);

        return view('event-type.edit', [
            'eventType' => $record,
        ]);
    }

    public function updates(LookupUpdateRequest $request, $id)
    {
        $request->validate([
            'event_type_name' => ['required', 'string', 'max:255'],
            'takes_reservation' => ['nullable', 'boolean'],
        ]);

        $config = $this->lookupConfig();

        DB::table($config['table'])
            ->where($config['primary_key'], $id)
            ->update($this->payload($config, $request));

        return $this->redirectWithMessage($config, 'update', $request);
    }

    protected function payload(array $config, Request $request, bool $includeDefaults = false): array
    {
        $payload = parent::payload($config, $request, $includeDefaults);
        $payload['TakesReservation'] = $request->boolean('takes_reservation') ? 1 : 0;

        return $payload;
    }
}
