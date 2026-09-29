<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BookingRequestController;

class AdminApprovalRequestController extends BookingRequestController
{
    protected string $scope = 'admin';
}
