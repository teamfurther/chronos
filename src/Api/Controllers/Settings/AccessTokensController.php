<?php

namespace Chronos\Api\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokensController extends Controller
{

    public function index(Request $request)
    {
        $itemsPerPage = Config::get('chronos.items_per_page');

        $q = PersonalAccessToken::query();

        // filter
        if ($request->has('filters')) {
            $filters = $request->get('filters');

            if (isset($filters['search']) && $filters['search'] != '') {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            }
        }

        // sort
        if ($request->has('sortBy') && $request->get('sortBy') != '') {
            $sortBy = $request->get('sortBy');
            $sortOrder = $request->get('sortOrder');

            switch ($sortBy) {
                case 'name':
                    $q->orderBy($sortBy, (isset($sortOrder) && $sortOrder === 'true') ? 'ASC' : 'DESC');
                    break;

                default:
                    return response()->json([
                        'alerts' => [
                            (object) [
                                'type' => 'error',
                                'title' => trans('chronos::alerts.Error.'),
                                'message' => trans('chronos::alerts.Invalid sortBy argument: :arg.', ['arg' => $sortBy]),
                            ]
                        ],
                        'status' => 200
                    ], 200);
            }
        }
        else
            $q->orderBy('name', 'ASC');

        // pagination
        $data = $q->paginate($itemsPerPage);

        // add endpoints and access token value
        if (count($data) > 0) {
            foreach ($data as &$item) {
                // add admin URLs
                $item->setAttribute('endpoints', ['destroy' => route('api.settings.access_tokens.destroy', ['token' => $item->id])]);

                // add access token value
                $item->setAttribute('access_token', $item->token);
            }
        }

        return response()->json($data, 200);
    }

    public function destroy(PersonalAccessToken $token)
    {
        if ($token->delete())
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'success',
                        'title' => trans('chronos::alerts.Success.'),
                        'message' => trans('chronos::alerts.Access token deletion was successful.'),
                    ]
                ],
                'status' => 200
            ], 200);
        else
            return response()->json([
                'alerts' => [
                    (object) [
                        'type' => 'error',
                        'title' => trans('chronos::alerts.Error.'),
                        'message' => trans('chronos::alerts.Access token deletion was unsuccessful.'),
                    ]
                ],
                'status' => 500
            ], 500);
    }

    public function store(Request $request)
    {
        // validate input
        $this->validate($request, [
            'name' => 'required|unique:personal_access_tokens'
        ]);

        // create access token
        $root = User::whereHas('role', function($q) {
            $q->where('name', 'root');
        })->first();
        $token = $root->createToken($request->get('name'));

        return response()->json([
            'alerts' => [
                (object) [
                    'type' => 'success',
                    'title' => trans('chronos::alerts.Success.'),
                    'message' => trans('chronos::alerts.Access token successfully created.'),
                ]
            ],
            'token' => $token->plainTextToken,
            'tokenMessage' => trans('chronos::alerts.Your token is: :token. Please copy it in a safe place.', [
                'token' => $token->plainTextToken
            ]),
            'status' => 200
        ], 200);
    }

}