<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20);

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        return view('staff.messages.index', compact('messages', 'unreadCount'));
    }

    public function show(ContactMessage $message)
    {
        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return view('staff.messages.show', compact('message'));
    }

    public function markRead(ContactMessage $message)
    {
        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return back()->with('success', 'Marked as read.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return redirect()->route('staff.messages.index')->with('success', 'Message deleted.');
    }
}
