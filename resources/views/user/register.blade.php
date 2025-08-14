@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Register - User')

@section('content')
<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6 mx-auto">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Create your account</h4>
        <form id="formRegister" class="mb-6" action="{{ route('user.register.submit') }}" method="POST">
          @csrf
          <div class="mb-6">
            <label for="firstname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstname" name="firstname" required autofocus />
          </div>
          <div class="mb-6">
            <label for="lastname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastname" name="lastname" required />
          </div>
          <div class="mb-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required />
          </div>
          <div class="mb-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required />
          </div>
          <div class="mb-6">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required />
          </div>
          <div class="mb-6">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required />
          </div>
          <button class="btn btn-primary d-grid w-100">Register</button>
        </form>
        <p class="text-center">
          <span>Already have an account?</span>
          <a href="{{ route('user.login') }}">
            <span>Sign in</span>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
