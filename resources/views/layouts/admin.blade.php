<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adi | {{ $title }}</title>

    <base href="{{ url('/') }}/" />
    <!-- Google Font: Source Sans Pro -->
    <link rel="shortcut icon" href="{{ asset('public/uploads/favicon.png') }}">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('assets/admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Datatable -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/toastr/toastr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/select2/select2.min.css') }}">

    <link rel="stylesheet"
        href="{{ asset('assets/admin/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">

    @yield('css')
    <style>
        #loader {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            width: 120px;
            height: 120px;
            margin: -76px 0 0 -76px;
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Add animation to "page content" */
        .animate-bottom {
            position: relative;
            -webkit-animation-name: animatebottom;
            -webkit-animation-duration: 1s;
            animation-name: animatebottom;
            animation-duration: 1s
        }

        @-webkit-keyframes animatebottom {
            from {
                bottom: -100px;
                opacity: 0
            }

            to {
                bottom: 0px;
                opacity: 1
            }
        }

        @keyframes animatebottom {
            from {
                bottom: -100px;
                opacity: 0
            }

            to {
                bottom: 0;
                opacity: 1
            }
        }

        #myDiv {
            display: none;
            text-align: center;
        }

        #barcodeView {
            height: 420px !important;
            overflow-y: auto;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed rightclickdisabled">
    <div class="wrapper">
        <!--<div id="loader"></div>-->

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ asset('assets/admin/img/logo.png') }}" alt="AdminLTELogo"
                height="130" width="130">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark navbar-dark">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" title="Sidebar" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->


                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="javascript:void(0)" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li>
                    <a class="nav-link btn btn-danger btn-sm" title="Logout" href="{{ route('logout') }}"
                        role="button">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-light-olive elevation-4">
            <!-- Brand Logo -->
            <a href="javascript:void(0)" class="brand-link bg-secondary">
                <img src="{{ isset(auth()->user()->image) ? asset('public/uploads/user/' . auth()->user()->image) : asset('assets/admin/img/user2-160x160.jpg') }}"
                    class="brand-image img-circle elevation-3" target="_blank"
                    style="height: 32px !important;width: 32px !important;margin-top: 0px !important;" alt="User Image">

                <span class="brand-text font-weight-light">{{ Auth::user()->first_name }}
                    {{ Auth::user()->last_name }}</span>
            </a>

            <!--Sidebar-->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    {{ __('lang.dashboard') }}
                                </p>
                            </a>
                        </li>

                        <?php if(!empty(checkRoles('categories','view_right')) && checkRoles('categories','view_right') != 0){ ?>
                        {{-- categories --}}
                        <li class="nav-item">
                            <a href="{{ route('category') }}"
                                class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                                <i class="fas fa-list-alt  nav-icon"></i>
                                <p>Categories</p>
                            </a>
                        </li>
                        <?php } ?>
                        {{-- returns --}}
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">{{ $title }}</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item active">{{ __('lang.dashboard') }}</li>
                                <li class="breadcrumb-item active">{{ $title }}</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                @yield('content')
            </section>
            <!-- /.content -->
        </div>


        <!-- /.content-wrapper -->
        <footer class="main-footer ">
            <div class="row">
                <div class="col-12 row">
                    <div class="col-lg-9">
                        <strong>Copyright &copy; {{ date('Y') }} SMS .</strong>
                        All rights reserved.
                    </div>
                    <!--<div class="col-lg-3">-->
                    <!--    <div class="text-right">-->
                    <!--        <a href="https://i-quall.com/" target="_blank"><img src="https://i-quall.com/wp-content/themes/iquall/images/new-images/logo.png" height="20" title="Design & Developed by I-Quall Infoweb"></a>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('assets/admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
        $(document).on("input", ".numeric", function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <!-- ChartJS -->
    <!-- <script src="{{ asset('assets/admin/plugins/chart.js/Chart.min.js') }}"></script> -->
    <!-- Sparkline -->
    <!-- <script src="{{ asset('assets/admin/plugins/sparklines/sparkline.js') }}"></script> -->
    <!-- JQVMap -->
    <!-- <script src="{{ asset('assets/admin/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script> -->
    <!-- jQuery Knob Chart -->
    <!-- <script src="{{ asset('assets/admin/plugins/jquery-knob/jquery.knob.min.js') }}"></script> -->
    <!-- daterangepicker -->
    <script src="{{ asset('assets/admin/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <!-- <script src="{{ asset('assets/admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}">
    </script> -->
    <!-- Summernote -->
    <script src="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('assets/admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>


    <script src="{{ asset('assets/admin/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/admin/plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <script src="{{ asset('assets/admin/plugins/toastr/toastr.min.js') }}"></script>

    <script src="{{ asset('assets/admin/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script src="{{ asset('assets/admin/plugins/select2/select2.full.min.js') }}"></script>

    <script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>



    @yield('scripts')
    <script>
        // $(".form-inline strong" ).removeClass("text-light");

        //
        // var element = document.querySelector('.form-inline .text-light');
        //   element.classList.remove("text-light");

        function func() {
            setTimeout(() => {
                console.log('ajshdkash');
            }, 500);
        }
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key == "p" || e.charCode == 16 || e.charCode == 112 || e.keyCode ==
                    80)) {
                //    alert("Please use the Print PDF button below for a better rendering on the document");
                e.cancelBubble = true;
                e.preventDefault();

                e.stopImmediatePropagation();
            }
        });

        $(document).ready(function() {
            $(".rightclickdisabled").on("contextmenu", function(e) {
                return false;
            });
        });

        // $('#loader').show();
    </script>
</body>

</html>
