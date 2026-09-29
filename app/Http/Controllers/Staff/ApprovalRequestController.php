<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\BookingRequestController;

class ApprovalRequestController extends BookingRequestController
{
    protected string $scope = 'staff';
}
