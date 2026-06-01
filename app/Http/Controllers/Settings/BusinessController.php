<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BusinessUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/business', [
            'business' => $request->user()->only([
                'company_name',
                'company_code',
                'vat',
                'address',
                'phone',
                'contact_person',
            ]),
        ]);
    }

    public function update(BusinessUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Business settings updated.')]);

        return to_route('business.edit');
    }
}
