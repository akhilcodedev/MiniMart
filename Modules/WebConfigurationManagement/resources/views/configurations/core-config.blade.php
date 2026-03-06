@extends('base::layouts.mt-main')

@section('content')

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Your App')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Layout Styles */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            color: #fff;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .inner-content {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="layout-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        @include('webconfigurationmanagement::inner-menu.sidebar')
    </aside>

    <!-- Main Content -->
    <div class="content">
        <main>
            <div class="inner-content">
                @yield('container')
            </div>
        </main>
    </div>
</div>
</body>
</html>

@stop

@section('script')
    <script>
        $(document).ready(function () {
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href"); // Get the target pane
                console.log("Activated tab:", target);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const menuLinks = document.querySelectorAll('.menu-link');
            const formContainers = document.querySelectorAll('.form-container');

            menuLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();

                    // Get the target form ID
                    const targetForm = this.getAttribute('data-target');

                    // Hide all forms
                    formContainers.forEach(form => {
                        form.style.display = 'none';
                    });

                    // Show the target form
                    const activeForm = document.getElementById(targetForm);
                    if (activeForm) {
                        activeForm.style.display = 'block';
                    }
                });
            });
        });
    </script>
@stop
