<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class MarketingUnsubscribeController extends Controller
{
    /**
     * Opt the user out of marketing emails.
     */
    public function __invoke(User $user): View
    {
        if ($user->marketing_opt_in) {
            $user->update([
                'marketing_opt_in' => false,
                'marketing_consented_at' => null,
            ]);
        }

        return view('marketing.unsubscribed');
    }
}
