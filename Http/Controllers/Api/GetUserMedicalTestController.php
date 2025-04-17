<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TopicResource;
use App\Models\File;
use App\Models\MedicalTest;
use App\Models\UserMedicalTest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class GetUserMedicalTestController extends Controller
{
     
    public function index(Request $request)
    {
        try {
            // Define the specific types
            $specificTypes = ['weight', 'glucose', 'pressure', 'heartRate', 'relaxHeartRate', 'temp', 'height'];

            // Get the authenticated user's ID
            $userId = auth()->id();

            // Fetch user medical tests
            $userMedicalTests = UserMedicalTest::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedTests = [];

            // Loop through each medical test
            foreach ($userMedicalTests as $test) {
                $type = $test->type;

                // Skip the test if its type is not in the specific types
                if (!in_array($type, $specificTypes)) {
                    $medicalTest = MedicalTest::with('articles')->find($test->medical_test_id);

                    // Check if the medical test exists
                    if (!$medicalTest) {
                        continue;
                    }

                    // Initialize the formatted data array if the type is not already present
                    if (!isset($formattedTests[$type])) {
                        $formattedTests[$type] = [
                            'type' => $type,
                            'user_id' => $test->user_id,
                            'unit' => $medicalTest->units,
                            'tests' => [],
                            'articles' => TopicResource::collection($medicalTest->articles),
                        ];
                    }
                } else {
                    // Initialize the formatted data array if the type is not already present and is in specific types
                    if (!isset($formattedTests[$type])) {
                        $formattedTests[$type] = [
                            'type' => $type,
                            'user_id' => $test->user_id,
                            'unit' => null,
                            'tests' => [],
                            'articles' => collect(),
                        ];
                    }
                }

                // Append the test data to the respective type
                $formattedTests[$type]['tests'][] = [
                    'value' => $test->value,
                    'value2' => $test->value2,
                    'date' => $test->date,
                    'created_at' => $test->created_at,
                ];

                // If the test has a corresponding medical test value, add it to the formatted data
                if ($test->medicalTestValue) {
                    $formattedTests[$type]['min'] = $test->medicalTestValue->min;
                    $formattedTests[$type]['max'] = $test->medicalTestValue->max;
                    $formattedTests[$type]['gender'] = $test->medicalTestValue->gender;
                }
            }

            // Convert the associative array to a numerically indexed array
            $formattedTests = array_values($formattedTests);

            // Paginate the formatted tests
            $perPage = $request->input('per_page', 10); // Number of items per page
            $currentPage = $request->input('page', 1); // Current page number

            $paginator = new LengthAwarePaginator(
                collect($formattedTests)->forPage($currentPage, $perPage),
                count($formattedTests),
                $perPage,
                $currentPage
            );

            // Get the pagination details
            $pagination = [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ];

            // Return the paginated tests along with pagination details
            return response()->json([
                'data' => $paginator->items(),
                'pagination' => $pagination
            ], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json(['error' => 'Failed to retrieve user medical tests'], 500);
        }
    }
}
