<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $locale }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ __('Shamandora Scout - Dashboard') }}</title>

    <!-- Custom fonts for this template-->
    <link href="../../../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <link rel="icon" type="image/webp" href={{ asset('img/shamandora.webp') }}>
    <!-- Custom styles for this template-->
    <link href="../../../css/sb-admin-2.css" rel="stylesheet">
    <link href="../../../css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="../../../../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>



<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar" style="right:0">
            <div>
                <img class ="" src="{{ asset('img/shamandora.webp') }}" style="width: 100px; height: 100px;"
                    alt="Shamandora Image">
            </div>
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-text mx-3">Shamandora Scouts</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span style="font-family: 'Cairo', sans-serif; font-weight: lighter;">{{ __('Dashboard') }}</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Settings</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Configurations</h6>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Configurations</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Configurations</h6>
                        <a class="collapse-item">Colors</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Summer
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-cog"></i>
                    <span style="font-family: 'Cairo', sans-serif;">{{ __('New enrolments') }}</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">{{ __('Login & registration pages') }}</h6>
                        <a class="collapse-item" href={{ url('/liveform') }}>{{ __('LIVE registration form') }}</a>
                        <a class="collapse-item" href={{ url('/new-enrolments') }}>{{ __('Review enrolment requests') }}</a>
                        <a class="collapse-item" href={{ url('/max-limits') }}>{{ __('Max request limits') }}</a>
                        <a class="collapse-item" href={{ url('/entry-questions') }}>{{ __('Sector questions control') }}</a>
                        <a class="collapse-item" href={{ url('/new-enrolments/analytics') }}>{{ __('Enrolment analytics') }}</a>
                        <a class="collapse-item" href={{ url('/new-enrolments/migrations') }}> تحويل الطلبات إلى النظام
                            الرئيسي</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link">
                    <i class="fas fa-fw fa-table"></i>
                    <span style="font-family: 'Cairo', sans-serif;">جدول الملتحقين</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Sidebar Message -->

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small"
                                style="font-family: 'Cairo', sans-serif; direction: rtl;" placeholder="{{ __('Search...') }}"
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header" style="font-family: 'Cairo', sans-serif;">
                                    الاشعارات
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to
                                            download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All
                                    Alerts</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Counter - Messages -->
                                <span class="badge badge-danger badge-counter">7</span>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header" style="font-family: 'Cairo', sans-serif;">
                                    الرسائل
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src={{ asset('img/undraw_profile_1.svg') }}
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>

                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src={{ asset('img/undraw_profile_2.svg') }}
                                            alt="...">
                                        <div class="status-indicator"></div>
                                    </div>

                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src={{ asset('img/undraw_profile_3.svg') }}
                                            alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>

                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle"
                                            src="https://source.unsplash.com/Mv9hjnEUHR4/60x60" alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>

                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More
                                    Messages</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"
                                    style="font-family: 'Cairo', sans-serif;">{{ Auth::user()->FirstName }}
                                    {{ Auth::user()->SecondName }}</span>
                                <img class="img-profile rounded-circle" src={{ asset('img/undraw_profile.svg') }}>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ Auth::user()->ShamandoraCode }}
                                </a>
                                <a class="dropdown-item">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>

                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <form class="dropdown-item" method="POST" action="{{ route('logout') }}">
                                    @csrf <!-- Include the CSRF token -->
                                    <button type="submit">Log Out</button>
                                </form>
                            </div>
                        </li>

                    </ul>

                </nav>

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
                                    <h6 class="m-0 font-weight-bold text-primary">تعديل الحد الأقصى</h6>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <form class="user" id="regForm" method="post"
                                    action="{{ route('max-limits.update', ['id' => $marhalaSelected->QetaaID, 'sana_id' => $marhalaSelected->SanaMarhalaID]) }}">
                                    @method('PATCH')
                                    @csrf
                                    <div class="card-header py-3">
                                        <div>
                                            <input style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{ $marhalaSelected->QetaaName }}" disabled></input>
                                            <br>
                                        </div>
                                        <div>
                                            <input style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{ $marhalaSelected->SanaMarhalaName }}" disabled></input>
                                            <br>
                                        </div>
                                        <div class="form-group row text-center" dir="{{ $dir }}">
                                            <label for="joindate"
                                                style="font-family: 'Cairo', sans-serif;">سنة</label>
                                            <br />
                                            <select class="form-control col-sm-4" style="margin-right: 20px;"
                                                name="year" id="year" onChange=""
                                                placeholder="اختار سنة الالتحاق">
                                                <option
                                                    style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                    value="2024" selected></option>
                                            </select>

                                        </div>
                                    </div>

                                    <div style="margin-left: 5px; margin-right: 5px">
                                        <label style="font-family: 'Cairo', sans-serif; color: black;">قيمة الحد الأقصى
                                            لطلبات السجيل</label>
                                        <input type="text" class="form-control" name="max_limit" id="max_limit"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium; line-height: 6em;"
                                            value="{{ $marhalaSelected->MaxLimit }}">

                                        <br>
                                        <hr>
                                        <input type="submit" class="btn-google btn-user btn-block"
                                            style="background-color: brown;" id="submit-button"
                                            value="تعديل"></input>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- /.container-fluid -->

                    </div>
                    <!-- End of Main Content -->

                    <!-- Footer -->
                    <footer class="sticky-footer bg-white">
                        <div class="container my-auto">
                            <div class="copyright text-center my-auto">
                                <span>Copyright &copy; Shamandora Scouts {{ date('Y') }}</span>
                                <br />
                                <span style="font-size: larger;font-weight: bold; color: #4e73df;">مجموعة الشمندورة
                                    الكشفية</span>
                            </div>
                        </div>
                    </footer>
                    <!-- End of Footer -->

                </div>
                <!-- End of Content Wrapper -->

            </div>
            <!-- End of Page Wrapper -->

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <!-- Logout Modal-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Select "Logout" below if you are ready to end your current session.
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" href="login.html">Logout</a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Bootstrap core JavaScript-->
            <script src="../../../vendor/jquery/jquery.min.js"></script>
            <script src="../../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="../../../../vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="../../../js/sb-admin-2.min.js"></script>

            <!-- Page level plugins -->
            <script src="../../../../vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="../../../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="../../../js/demo/datatables-demo.js"></script>

            <script>
                var globalIncrement = 0;

                function clicked() {
                    var dropdown = document.getElementById('required_answer_type');
                    var text = dropdown.options[dropdown.selectedIndex].value;
                    console.log(text);

                    if (text === "MultipleChoice") {


                        console.log('Inside Multiple Choice Options');
                        var container = document.getElementById("container");
                        container.appendChild(document.createElement("br"));
                        while (container.hasChildNodes()) {
                            container.removeChild(container.lastChild);
                        }

                        var labelA = document.createElement("label");
                        labelA.innerHTML = " أدخل عدد الاختيارات المطلوبة";
                        container.appendChild(labelA);
                        container.appendChild(document.createElement("br"));
                        var labelB = document.createElement("label");
                        labelB.innerHTML = "(بحد أقصى 6 اختيارات فقط)";
                        labelB.style.fontWeight = "bold";
                        container.appendChild(labelB);


                        container.appendChild(document.createElement("br"));

                        var input = document.createElement("input");
                        input.type = "text";
                        input.name = "memberA";
                        input.id = "memberA";
                        input.placeholder = "";
                        input.onchange = "";
                        input.setAttribute("class", "form-control");
                        container.appendChild(input);

                        container.appendChild(document.createElement("br"));
                        container.appendChild(document.createElement("hr"));

                        var a = document.createElement("input");
                        a.type = "button";
                        a.id = "filldetails";
                        a.setAttribute("onclick", "addFields()");
                        a.value = "اضغط لاضافة تفاصيل الاختيارات";

                        container.appendChild(a);

                        container.appendChild(document.createElement("br"));

                    } else {
                        console.log('Inside Else Options');
                        var container = document.getElementById("container");
                        while (container.hasChildNodes()) {
                            container.removeChild(container.lastChild);
                        }
                    }
                }

                function addFields() {
                    console.log(globalIncrement);
                    if (globalIncrement != 0) {
                        for (var i = 1; i <= globalIncrement; i++) {
                            document.getElementById("choice" + i).remove();
                            document.getElementById("label" + i).remove();
                            document.getElementById("br" + i).remove();
                            document.getElementById("brx" + i).remove();
                            console.log("Removing choice" + i + " , label" + i + " , br" + i + " , brx" + i);
                        }
                        globalIncrement = 0;
                    }

                    var numberOfChoices = document.getElementById("memberA").value;
                    if (numberOfChoices > 6)
                        numberOfChoices = 6;
                    console.log("Input Function");
                    console.log(numberOfChoices);
                    globalIncrement = numberOfChoices;

                    for (var i = 1; i <= numberOfChoices; i++) {
                        var label = document.createElement("label");
                        label.id = "label" + i;
                        label.innerHTML = "اختيار رقم: " + i;
                        container.appendChild(label);
                        var br = document.createElement("br");
                        br.id = "br" + i;
                        container.appendChild(br);
                        var input = document.createElement("input");
                        input.type = "text";
                        input.name = "choice" + i;
                        input.id = "choice" + i;
                        container.appendChild(input);
                        // Append a line break 
                        var brx = document.createElement("br");
                        brx.id = "brx" + i;
                        container.appendChild(brx);


                    }

                    function increaseAnswers() {

                    }
                }
            </script>

</body>

</html>
