<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = ContactMessage::query()
            ->whereNull('read_at')
            ->count();

        return view('staff.messages.index', compact('messages', 'unreadCount'));
    }

    public function show(ContactMessage $message)
    {
        // ✅ inbox behavior: opening marks as read
        if (is_null($message->read_at)) {
            $message->forceFill(['read_at' => now()])->save();
        }

        // optional (useful for badges in page header)
        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        return view('staff.messages.show', compact('message', 'unreadCount'));
    }

    public function markRead(Request $request, ContactMessage $message)
    {
        if (is_null($message->read_at)) {
            $message->forceFill(['read_at' => now()])->save();
        }

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Marked as read.',
                'unreadCount' => $unreadCount,
                'read_at' => optional($message->read_at)->toIso8601String(),
            ]);
        }

        return $this->ktRedirectToReturn($request, 'staff.messages.index')
            ->with('success', 'Marked as read.');
    }

    public function restore(Request $request, int $id)
    {
        $message = ContactMessage::withTrashed()->findOrFail($id);
        $message->restore();

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Message restored.',
                'unreadCount' => $unreadCount,
            ]);
        }

        return $this->ktRedirectToReturn($request, 'staff.messages.index')
            ->with('success', 'Message restored successfully!');
    }

    public function destroy(Request $request, ContactMessage $message)
    {
        $label = 'Message #' . $message->id;

        $message->delete();

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Message deleted.',
                'unreadCount' => $unreadCount,
                'undoUrl' => route('staff.messages.restore', [
                    'id' => $message->id,
                    'return' => $this->ktReturnUrl($request, 'staff.messages.index'),
                ]),
            ]);
        }

        $returnUrl = $this->ktReturnUrl($request, 'staff.messages.index');

        return $this->ktRedirectToReturn($request, 'staff.messages.index')
            ->with('success', 'Message deleted.')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.messages.restore', ['id' => $message->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }

    /**
     * ✅ AJAX widget endpoint for realtime polling
     *
     * Query params:
     * - limit=20 (1..50)
     * - since=ISO8601 timestamp (optional)
     * - since_id=123 (optional, preferred for accuracy/efficiency)
     *
     * Returns JSON:
     * - unreadCount
     * - latest[] (most recent messages, length <= limit)
     * - newCount
     * - new[] (messages newer than since/since_id, length <= limit)
     */
    public function widget(Request $request)
    {
        // If you want to strictly lock this to AJAX:
        // (prevents the endpoint being abused from the browser address bar)
        if (!$request->expectsJson() && !$request->ajax()) {
            abort(404);
        }

        $limit = (int) $request->integer('limit', 20);
        if ($limit < 1) $limit = 1;
        if ($limit > 50) $limit = 50;

        $unreadCount = ContactMessage::query()
            ->whereNull('read_at')
            ->count();

        // Build latest list
        $latestModels = ContactMessage::query()
            ->latest()
            ->take($limit)
            ->get();

        $latest = $latestModels->map(function (ContactMessage $m) {
            return [
                'id' => $m->id,
                'name' => (string) $m->name,
                'email' => (string) $m->email,
                'message' => (string) $m->message,
                'preview' => Str::limit((string) $m->message, 120),
                'is_unread' => $m->read_at === null,
                'created_at_iso' => optional($m->created_at)->toIso8601String(),
                'created_at' => optional($m->created_at)->format('M d, Y h:i A'),
                'show_url' => route('staff.messages.show', $m),
            ];
        })->values();

        // Determine "new" messages since the last poll
        $newModelsQuery = ContactMessage::query()->latest();

        $newCount = 0;
        $new = collect();

        // Prefer since_id (more stable than timestamps)
        $sinceId = (int) $request->query('since_id', 0);
        if ($sinceId > 0) {
            $newModelsQuery = ContactMessage::query()
                ->where('id', '>', $sinceId)
                ->latest();

            $newModels = $newModelsQuery->take($limit)->get();
            $newCount = ContactMessage::query()->where('id', '>', $sinceId)->count();

            $new = $newModels->map(function (ContactMessage $m) {
                return [
                    'id' => $m->id,
                    'name' => (string) $m->name,
                    'email' => (string) $m->email,
                    'message' => (string) $m->message,
                    'preview' => Str::limit((string) $m->message, 120),
                    'is_unread' => $m->read_at === null,
                    'created_at_iso' => optional($m->created_at)->toIso8601String(),
                    'created_at' => optional($m->created_at)->format('M d, Y h:i A'),
                    'show_url' => route('staff.messages.show', $m),
                ];
            })->values();
        } else {
            $since = $request->query('since');
            if (!empty($since)) {
                try {
                    $sinceDt = \Carbon\Carbon::parse($since);

                    $newCount = ContactMessage::query()
                        ->where('created_at', '>', $sinceDt)
                        ->count();

                    $newModels = ContactMessage::query()
                        ->where('created_at', '>', $sinceDt)
                        ->latest()
                        ->take($limit)
                        ->get();

                    $new = $newModels->map(function (ContactMessage $m) {
                        return [
                            'id' => $m->id,
                            'name' => (string) $m->name,
                            'email' => (string) $m->email,
                            'message' => (string) $m->message,
                            'preview' => Str::limit((string) $m->message, 120),
                            'is_unread' => $m->read_at === null,
                            'created_at_iso' => optional($m->created_at)->toIso8601String(),
                            'created_at' => optional($m->created_at)->format('M d, Y h:i A'),
                            'show_url' => route('staff.messages.show', $m),
                        ];
                    })->values();
                } catch (\Throwable $e) {
                    $newCount = 0;
                    $new = collect();
                }
            }
        }

        $latestId = (int) optional($latestModels->first())->id;

        return response()->json([
            'ok' => true,
            'unreadCount' => $unreadCount,
            'newCount' => $newCount,
            'latestId' => $latestId,
            'serverTime' => now()->toIso8601String(),
            'latest' => $latest,
            'new' => $new,
        ]);
    }
}
