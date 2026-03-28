<!DOCTYPE html>
<html lang="ar">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>كشافة الشمندورة - لوحة التحكم</title>

    <!-- Custom fonts for this template-->
    <link href="../../../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <link rel="icon" type="image/x-icon" href={{ asset('img/shamandora.png') }}>
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
                <img class ="" src="{{ asset('img/shamandora.png') }}" style="width: 100px; height: 100px;"
                    alt="Shamandora Image">
            </div>
            <!-- Sidebar - Brand -->
            <label class="sidebar-brand d-flex align-items-center justify-content-center" href={{ url('/index') }}>
                <div class="sidebar-brand-text mx-3">Shamandora Scouts</div>
            </label>



            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Summer 2024
            </div>



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
                            <h1 class="h3 mb-2 text-gray-800" style="font-family: 'Cairo', sans-serif;">الملتحقين
                                الجدد </h1>

                            <!-- DataTales Example -->
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable"
                                            style="table-layout: auto; border-collapse: collapse; width: 100%;"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>الطلب</th>
                                                    <th> الاسم</th>
                                                    <th>المرحلة</th>
                                                    <th>القطاع</th>
                                                    <th>الرقم القومي</th>
                                                    <th>رقم الموبايل</th>
                                                    <th>هل أكمل الأسئلة؟</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($persons as $person)
                                                    <tr>
                                                        <td>
                                                            {{ $person->PersonID }}
                                                        </td>

                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="personIDLabel-{{ $loop->iteration }}">{{ $person->FirstName }}
                                                                {{ $person->SecondName }} {{ $person->ThirdName }}
                                                                {{ $person->FourthName }}</label>
                                                        </td>
                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="joiningYearId-{{ $loop->iteration }}">{{ $person->SanaMarhalaName }}</label>
                                                        </td>
                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="joiningYearId-{{ $loop->iteration }}">{{ $person->QetaaName }}</label>
                                                        </td>
                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="joiningYearId-{{ $loop->iteration }}">{{ $person->RaqamQawmy }}</label>
                                                        </td>
                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="mobileNumberId-{{ $loop->iteration }}">{{ $person->PersonPersonalMobileNumber }}</label>
                                                        </td>
                                                        <td>
                                                            <label style="color: #4e73df; font-weight: bolder;"
                                                                id="mobileNumberId-{{ $loop->iteration }}">{{ $person->HasAnsweredQuestions }}</label>
                                                        </td>
                                                        <td>
                                                            @if ($person->IsApproved == 0)
                                                                <a href="{{ route('person.new-enrolments-approve', $person->PersonID) }}"
                                                                    style="appearance: none;
                                                                background-color: #2ea44f;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                                                                    موافقة</a>
                                                            @else
                                                                <a
                                                                    style="appearance: none;
                                                        background-color: #dcdf29;
                                                        border: 1px solid rgba(27, 31, 35, .15);
                                                        border-radius: 6px;
                                                        box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                        box-sizing: border-box;
                                                        color: #fff;
                                                        cursor: pointer;
                                                        display: inline-block;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        line-height: 20px;
                                                        padding: 6px 16px;
                                                        position: relative;
                                                        text-align: center;
                                                        text-decoration: none;
                                                        user-select: none;
                                                        -webkit-user-select: none;
                                                        touch-action: manipulation;
                                                        vertical-align: middle;
                                                        white-space: nowrap;">
                                                                    Done!</a>
                                                            @endif
                                                            <a href="{{ route('person.new-enrolments-delete', $person->PersonID) }}"
                                                                style="appearance: none;
                                                                background-color: #E21739;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                                                                رفض</a>

                                                            <a href="{{ route('person.new-enrolments-show', $person->PersonID) }}"
                                                                style="appearance: none;
                                                                background-color: #202DDF;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                                                                عرض</a>
                                                            @if ($person->HasAnsweredQuestions == 'لا')
                                                                <a href="{{ route('person.entry-questions-liveform', $person->PersonID) }}"
                                                                    style="appearance: none;
                                                                background-color: #6e0d5e;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                                                                    اكمال الاسئلة</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if (auth()->user()->role[0]->RoleName == 'SuperAdmin')
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 1) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #e41359;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع براعم)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 2) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #13a2e4;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع أشبال)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 6) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #e4e013;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع مرشدات)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 8) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #13e46a;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع كشافة)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 9) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #8613e4;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع زهرات)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 3) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #367E41;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع متقدم)</a>
                                        </div>
                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 4) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #e41359;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (قطاع رائدات)</a>
                                        </div>

                                        <div>
                                            <a href="{{ route('person.migrate-new-enrolments', 10) }}"
                                                style="appearance: none;
                                            margin-bottom: 5px;
                                            background-color: #000000;
                                            border: 1px solid rgba(27, 31, 35, .15);
                                            border-radius: 6px;
                                            box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                            box-sizing: border-box;
                                            color: #fff;
                                            cursor: pointer;
                                            display: inline-block;
                                            font-size: 14px;
                                            font-weight: 600;
                                            line-height: 20px;
                                            padding: 6px 16px;
                                            position: relative;
                                            text-align: center;
                                            text-decoration: none;
                                            user-select: none;
                                            -webkit-user-select: none;
                                            touch-action: manipulation;
                                            vertical-align: middle;
                                            white-space: nowrap;"
                                                id="s">ادخال جميع الاشخاص التي تم الموافقة عليهم إلى النظام
                                                الأساسي (مدرسة اعداد قادة)</a>
                                        </div>
                                    @endif
                                </div>
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
                            <a class="btn btn-primary" href="{{ route('logout') }}">Logout</a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Bootstrap core JavaScript-->
            <script src="../../../../vendor/jquery/jquery.min.js"></script>
            <script src="../../../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="../../../vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="../../../js/sb-admin-2.min.js"></script>

            <!-- Page level plugins -->
            <script src="../../../vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="../../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="../../../js/demo/datatables-demo.js"></script>

            <script>
                function EditButtonClicked(itemNumber) {
                    // Retrieve the item data based on the itemNumber
                    // Enable editing for the corresponding item
                    console.log(`Editing item ${itemNumber}`);
                    document.getElementById('rotbaIDTextBox-' + itemNumber).removeAttribute("readonly");
                    document.getElementById('SubmitButtonNumber-' + itemNumber).removeAttribute("hidden");
                    document.getElementById('EditButtonNumber-' + itemNumber).disabled = true;
                    // Implement your custom logic here
                }

                function SubmitButtonClicked(itemNumber) {
                    // Retrieve the item data based on the itemNumber
                    // Enable editing for the corresponding item
                    console.log(`Submitting item ${itemNumber}`);
                    document.getElementById('EditButtonNumber-' + itemNumber).disabled = false;
                    document.getElementById('rotbaIDTextBox-' + itemNumber).disabled = true;
                    // Implement your custom logic here
                }
            </script>

</body>

</html>
