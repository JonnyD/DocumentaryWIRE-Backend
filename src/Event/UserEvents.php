<?php

namespace App\Event;

class UserEvents
{
    const USER_JOINED = "user.user_joined";
    const USER_CONFIRMED = "user.user_confirmed";
    const USER_CREATED_BY_ADMIN = "user.user_created_by_admin";
    const USER_FORGOT_USERNAME = "user.user_forgot_username";
    const USER_FORGOT_PASSWORD = "user.user_forgot_password";
    const USER_CHANGE_PASSWORD = "user.user_change_password";
    const USER_RESEND_CONFIRMATION_KEY = "user.user_resend_confirmation_key";
}