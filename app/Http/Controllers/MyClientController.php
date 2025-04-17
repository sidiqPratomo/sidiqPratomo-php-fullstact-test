<?php

namespace App\Http\Controllers;

use App\Models\my_client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MyClientController extends Controller
{
    public function index()
    {
        $clients = my_client::all();
        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:my_client,slug',
            'client_prefix' => 'required|string|max:4',
            'is_project' => 'required|in:0,1',
            'self_capture' => 'required|string|max:1',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'city' => 'nullable|string',
            'client_logo' => 'nullable|image|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        $clientLogo = 'no-image.jpg';
        if ($request->hasFile('client_logo')) {
            $clientLogo = $request->file('client_logo')->store('client_logos', 's3');
        }

        $client = my_client::create([
            'name' => $validatedData['name'],
            'slug' => $validatedData['slug'],
            'client_prefix' => $validatedData['client_prefix'],
            'is_project' => $validatedData['is_project'],
            'client_logo' => $clientLogo,
            'address' => $validatedData['address'],
            'phone_number' => $validatedData['phone_number'],
            'city' => $validatedData['city'],
        ]);

        Redis::set($client->slug, json_encode($client));

        return response()->json($client, 201);
    }

    public function show($slug)
    {
        $client = Redis::get($slug);
        if (!$client) {
            $client = my_client::where('slug', $slug)->first();
            if ($client) {
                Redis::set($slug, json_encode($client));
            } else {
                return response()->json(['message' => 'Client not found'], 404);
            }
        }

        return response()->json(json_decode($client, true));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        $client = my_client::where('slug', $slug)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        Redis::del($slug);

        if ($request->hasFile('client_logo')) {
            $clientLogo = $request->file('client_logo')->store('client_logos', 's3');
            $client->client_logo = $clientLogo;
        }

        $client->update($request->only([
            'name',
            'address',
            'phone_number',
            'city'
        ]));

        Redis::set($slug, json_encode($client));

        return response()->json($client);
    }

    public function softDelete($slug)
    {
        $client = my_client::where('slug', $slug)->firstOrFail();
        $client->update(['deleted_at' => now()]);

        Redis::del($slug);

        return response()->json(['message' => 'Client deleted (soft)']);
    }

    public function destroy($slug)
    {
        $client = my_client::where('slug', $slug)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        Redis::del($slug);

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
