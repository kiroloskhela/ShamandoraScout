<!DOCTYPE html>
<html lang="ar">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ __('Shamandora Scout - Dashboard') }}</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
        <style>
  @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">
    <!-- Custom styles for this template-->
    <link href="../../css/sb-admin-2.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    
</head>



<body id="page-top">


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">



                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800" style="font-family: 'Cairo', sans-serif;">{{ __('Control data') }}</h1>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">هل انت متأكد من حذف وجود حد أقصى لطلبات التقديم في هذه المرحلة؟</h6>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <form class="user" id="regForm" method="post" action="{{ route('max-limits.destroy', ['id'=>$selectedMarhala->QetaaID, 'sana_id'=>$selectedMarhala->SanaMarhalaID]) }}">
                            @method('DELETE')
                            @csrf
                            <div class="card-header py-3">
                                <div class="col-sm-3 mb-3 mb-sm-0">
                                                <input type="submit" class="btn-google btn-user btn-block" style="background-color: brown;" id="submit-button" value="حذف"></input>
                                                <a href="{{ route('max-limits.index') }}" class="btn-google btn-user btn-block" style="background-color: rgb(24, 32, 156); text-align: center">{{ __('Back') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        
        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/demo/datatables-demo.js"></script>

    <script>
/*    function myFunction() {
        const first_name = document.getElementById('rotba_name');
        if(first_name.value=='') {
        first_name.style.backgroundColor = '#C53939';
        first_name.style.color = '#FFFFFF';
        document.getElementById('submit-button').disabled = true;
        }
        else {
            first_name.style.backgroundColor = 'White';
            first_name.style.color = '#1D43EC';
        }
    }

    function clickSubmitButton(){
        const rotba_name = document.getElementById('rotba_name');
        if(rotba_name.value==''){
            alert("الرجاء ادخال البيانات بشكل صحيح");
                return false;
        }
    }
    */
</script>

</body>

</html>