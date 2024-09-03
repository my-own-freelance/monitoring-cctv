<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Admin cctv PIP</title>
    <!-- <link rel="stylesheet" href="css/style.css">-->
    <link rel="stylesheet" href="{{ asset('auth/css/styles.css') }}" rel="stylesheet">
    <style>
        body,
        html {
            width: 100%;
        }

        .bg {
            /* The image used */
            background-image: url("{{ asset('auth/images/bgpattern.jpg') }}");

            /* Full height */
            height: 100%;

            /* Center and scale the image nicely */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="bg">
    <!--Google Font - Work Sans-->
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>

    <div class="container">
        <div class="profile">
            <button class="profile__avatar" id="toggleProfile">
                <img style="height:170px" src="{{ asset('auth/images/logo.png') }}" alt="Avatar" />
            </button>
            <form id="formLogin">
                <div class="profile__form">
                    <div class="profile__fields">
                        <div class="field">
                            <input type="text" id="username" name="username" placeholder="Username" class="input"
                                required pattern=.*\S.* />
                        </div>
                        <div class="field">
                            <input type="password" id="password" name="password" placeholder="Password" class="input"
                                required pattern=.*\S.* />
                        </div>
                        <div class="field">
                            <div class="g-recaptcha" data-sitekey="{{ env('recaptcha2.key') }}"></div>
                        </div>
                    </div>
                    <div class="profile__footer">
                        <button type="submit" name="submit" class="btn">Log in</button>
                    </div>
            </form>
            <button class="btn" style="margin:10px auto; display:block;">
                <a href="{{ url('/') }}/file/apk/com.pip.cctvpip.apk" style="text-decoration: none; color:white;"
                    target="_blank">Download test</a>
            </button>
        </div>
    </div>
    <script src="{{ asset('dashboard/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.getElementById('toggleProfile').addEventListener('click', function() {
            [].map.call(document.querySelectorAll('.profile'), function(el) {
                el.classList.toggle('profile--open');
            });
        });

        $("#formLogin").submit(function(e) {
            e.preventDefault();

            let captchaResponse = grecaptcha.getResponse();
            if (!captchaResponse) {
                Swal.fire({
                    title: 'Peringatan',
                    text: "Silakan selesaikan reCAPTCHA.",
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
                return;
            }

            let dataToSend = $(this).serialize() + "&g-recaptcha-response=" + captchaResponse;
            submitAuth(dataToSend);
            return false;
        })

        function submitAuth(data) {
            $.ajax({
                url: "/api/auth/login/validate",
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    Swal.fire({
                        title: 'Selamat',
                        text: res.message,
                        icon: 'success',
                        confirmButtonText: 'Tutup'
                    });
                    setTimeout(() => {
                        window.location.href = "{{ route('dashboard') }}"
                    }, 1500)

                },
                error: function(err) {
                    console.log("error :", err)
                    Swal.fire({
                        title: 'Peringatan',
                        text: err.message || err.responseJSON
                            ?.message,
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            })
        }
    </script>

</body>

</html>
