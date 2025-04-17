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
        $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100',
            'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        $clientLogo = null;
        if ($request->hasFile('client_logo')) {
            $clientLogo = $request->file('client_logo')->store('client_logos', 's3');
        }

        $client = my_client::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'client_logo' => $clientLogo ?? 'no-image.jpg',
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
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
