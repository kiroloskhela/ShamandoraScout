<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>كشافة الشمندورة - التحاق جديد</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href={{ asset('img/shamandora.png') }}>
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-sm-5">
                        <img src={{ asset('img/shamandora.png') }} style="width: 100%; height: 100%">
                    </div>
                    <div class="col-sm-7">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">التحاق جديد</h1>
                            </div>
                            <form class="user" id="regForm" method="POST"
                                action="{{ route('person.liveform-insert') }}">
                                @csrf
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال
                                        بيانات ملتحق جديد</h1>
                                    <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                        الأول: البيانات الدراسية</h2>
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="sana_marhala_id" style="font-family: 'Cairo', sans-serif;">السنة
                                        والمرحلة الدراسية</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px;" name="sana_marhala_id"
                                        id="sana_marhala_id" onselect="checkMarhala()"
                                        placeholder="اختار السنة والمرحلة الدراسية"
                                        onclick="validate('sana_marhala_id')" onfocusout="validate('sana_marhala_id')">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر السنة والمرحلة الدراسية</option>
                                        @foreach ($seneen_marahel as $sana_marhala)
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{ $sana_marhala->SanaMarhalaID }}">
                                                {{ $sana_marhala->SanaMarhalaName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group text-center" dir="rtl">
                                    <label for="joindate" style="font-family: 'Cairo', sans-serif;">اختر نوع الملتحق
                                        <strong>(ذكر أم أنثى)</strong></label>
                                    <br />
                                    <select class="form-control" name="gender" id="gender" onChange=""
                                        placeholder="اختر النوع">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected>اختر النوع</option>
                                        <option style="font-family: 'Cairo', sans-serif; color: black;" value="Male">
                                            ذكر</option>
                                        <option style="font-family: 'Cairo', sans-serif; color: black;" value="Female">
                                            أنثى</option>
                                    </select>
                                </div>

                                <div class="form-group text-center" dir="rtl">
                                    <label>تقديم لمدرسة اعداد قادة؟</label>
                                    <input type="checkbox" name="newLeadersSchool" checked='' />
                                </div>

                                <input type="submit" class="btn btn-primary btn-user btn-block" id="submit-button"
                                    value="اضغط للاستمرار"></input>
                                <hr>
                            </form>

                            <div class="container my-auto">
                                <div class="copyright text-center my-auto">
                                    <span>Copyright &copy; Shamandora Scout 2024</span>
                                    <br />
                                    <span style="font-size: larger;font-weight: bold; color: #4e73df;">مجموعة الشمندورة
                                        الكشفية</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <script>
        function validate(ElementId) {
            const element = document.getElementById(ElementId);
            if (element.value == '') {
                //element.style.backgroundColor = '#C53939';
                //element.style.color = '#FFFFFF';
                document.getElementById('submit-button').disabled = true;
            } else {
                //element.style.backgroundColor = 'White';
                //element.style.color = '#1D43EC';
                document.getElementById('submit-button').disabled = false;
            }
        }
    </script>

</body>

</html>
