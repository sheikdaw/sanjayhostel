@extends('layouts.app')

@section('title', 'Sanjay PG Hostel — Mens & Womens Accommodation')

@push('styles')
    <style>
        /* YOUR CSS STAYS EXACTLY SAME */
    </style>
@endpush

@section('content')
<div class="rv-root">

    {{-- LEFT PANEL --}}
    <div class="rv-left">
        <div class="rv-lines"></div>
        <div class="rv-pulse"></div>

        <div class="rv-brand">
            <div class="rv-brand-icon">
                <i class="fas fa-hotel"></i>
            </div>
            <div>
                <div class="rv-brand-name">Sanjay PG</div>
                <div class="rv-brand-sub">Mens & Womens Hostel</div>
            </div>
        </div>

        <div class="rv-hero">
            <div class="rv-gold-tag">
                <span class="rv-gold-dot"></span>
                Premium Accommodation | Safe & Comfortable
            </div>

            <h1>Sanjay PG<br>Hostel<br><span>Mens & Womens</span></h1>

            <p>Authorised residents only. Secure hostel management system.</p>
        </div>

        <div class="rv-secure">
            <i class="fas fa-shield-alt"></i>
            Secure · Safe · Home Away From Home
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="rv-right">

        <div class="rv-form-wrap">

            <div class="rv-form-eyebrow">Sanjay PG | Resident Portal</div>
            <div class="rv-form-title">Sign In</div>
            <div class="rv-form-sub">Enter credentials to access your accommodation dashboard</div>

            <div class="rv-form-divider"></div>

            <form id="LoginForm">
                @csrf

                {{-- EMAIL --}}
                <div class="rv-field">
                    <label class="rv-label">Email</label>

                    <div class="rv-input-box">
                        <i class="fas fa-envelope rv-input-icon"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               class="rv-input"
                               placeholder="resident@domain.com">

                    </div>

                    <div class="rv-alert error d-none" id="email_error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="email_msg"></span>
                    </div>
                </div>

                {{-- PASSWORD --}}
                <div class="rv-field">
                    <label class="rv-label">
                        <span>Password</span>
                    </label>

                    <div class="rv-input-box">
                        <i class="fas fa-key rv-input-icon"></i>

                        <input type="password"
                               id="password"
                               name="password"
                               class="rv-input"
                               placeholder="••••••••">

                    </div>

                    <div class="rv-alert error d-none" id="password_error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="password_msg"></span>
                    </div>
                </div>

                {{-- BUTTON --}}
                <button type="submit" class="btn rv-submit" id="rvLoginBtn">
                    <span class="rv-submit-shimmer"></span>

                    <div class="rv-submit-inner">
                        <span>Access Resident Portal</span>
                        <span class="rv-submit-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>

                    <div class="rv-spinner"></div>
                </button>

            </form>

            <div class="rv-form-footer ">
                New resident? <a href="{{route('register')}}">Register here</a> | Forget password? <a href="{{route('forgetemail')}}">Reset here</a>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // =========================
    // CLEAR ERRORS
    // =========================
    function clearErrors() {
        $(".rv-alert.error").addClass("d-none");
        $(".email_msg, .password_msg").text("");
        $("#rv_email, #rv_password").removeClass("is-invalid");
    }

    // =========================
    // LOGIN FORM
    // =========================
    $("#LoginForm").submit(function (e) {
        e.preventDefault();

        clearErrors();

        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('login.submit') }}",
            type: "POST",
            data: formData,

            beforeSend: function () {
                $("#rvLoginBtn")
                    .prop("disabled", true)
                    .addClass("loading")
                    .html('<i class="fas fa-spinner fa-spin"></i> Please wait...');
            },

            success: function (response) {

                if (response.status) {

                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: response.message
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    });

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });

                }
            },

            error: function (xhr) {

                let message = "Something went wrong";

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                if (xhr.status === 422) {

                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (key, value) {

                        if (key === "email") {
                            $("#email_error").removeClass("d-none");
                            $("#email_error .email_msg").text(value[0]);
                            $("#email").addClass("is-invalid");
                        }

                        if (key === "password") {
                            $("#password_error").removeClass("d-none");
                            $("#password_error .password_msg").text(value[0]);
                            $("#password").addClass("is-invalid");
                        }

                    });
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: message
                });
            },

            complete: function () {
                $("#rvLoginBtn")
                    .prop("disabled", false)
                    .removeClass("loading")
                    .html(`
                        <span class="rv-submit-shimmer"></span>
                        <div class="rv-submit-inner">
                            <span>Access Resident Portal</span>
                            <span class="rv-submit-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                        <div class="rv-spinner"></div>
                    `);
            }
        });

    });

});
</script>
@endpush
