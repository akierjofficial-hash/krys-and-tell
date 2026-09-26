<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDeletedAccountController extends Controller
{
    public function index(Request $request)
    {
        $type = in_array($request->type, ['clinic', 'website'], true) ? $request->type : '';
        $q = trim((string) $request->q);
        $accounts = User::onlyTrashed()
            ->when($type === 'clinic', fn ($query) => $query->whereIn('role', ['admin', 'staff']))
            ->when($type === 'website', fn ($query) => $query->where('role', 'user'))
            ->when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")))
            ->latest('deleted_at')->paginate(20)->withQueryString();
        return view('admin.deleted_accounts.index', compact('accounts', 'type', 'q'));
    }
}
