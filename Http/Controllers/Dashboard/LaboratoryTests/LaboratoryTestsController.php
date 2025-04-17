<?php

namespace App\Http\Controllers\Dashboard\LaboratoryTests;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTests;
use App\Models\MedicalTest;
use App\Models\UserMedicalTest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class LaboratoryTestsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $LaboratoryTests = LaboratoryTests::whereNotNull('id')->orderBy('id', 'DESC')->get();
        $medical_tests = MedicalTest::whereNotNull('name')->get();
        $user_medical_tests = UserMedicalTest::whereNotNull('laboratory_id')->get();
        return view('admin.laboratory_tests.index', compact('LaboratoryTests', 'medical_tests', 'user_medical_tests'));
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Retrieve the LaboratoryTests record along with its related files
            $LaboratoryTests = LaboratoryTests::with('files')->findOrFail($id);

            $medical_tests = MedicalTest::with(['values', 'units.subUnits'])
                ->whereNotNull('name')
                ->whereHas('values', function ($query) {
                    $query->whereNotNull('id');
                })
                ->get();

            $user_medical_tests = UserMedicalTest::where('user_id', $LaboratoryTests->user_id)
                ->latest()
                ->get();
            // Initialize an array to store file paths
            $filePaths = [];

            // Loop through each file associated with the test
            foreach ($LaboratoryTests->files as $file) {
                // Access the file path from the related file
                $filePath = $file->file_path;

                // Get the absolute file path
                $absoluteFilePath = Storage::path($filePath);
                // $absoluteFilePath = Storage::url($filePath);

                $fileName = basename($filePath);
                // Add the absolute file path to the array
                $filePaths[] = $fileName;
            }

            // Return the files as a response
            // return $LaboratoryTests;
            return view('admin.laboratory_tests.show', compact('LaboratoryTests', 'filePaths', 'medical_tests', 'user_medical_tests'));
        } catch (\Exception $e) {
            // Handle exceptions
			return 'error'; 
        }
    }

    // For route to show pdf
    public function showPdf($filename)
    {
        // Get the absolute file path using the Storage facade
        $filePath = Storage::path("public/LaboratoryTests/PDF/{$filename}");

        // Check if the file exists
        if (!Storage::exists("public/LaboratoryTests/PDF/{$filename}")) {
            abort(404);
        }

        // Return the PDF file as a response
        return Response::file($filePath);
    }


    // For form left side
    public function SaveUserMedicalTest(Request $request)
    {

        $validated = $request->validate([
            'medical_test_id' => 'required|int',
            'value' => 'required|int',
            'laboratory_id' => 'required|int',
            'user_id' => 'required|int',
            'unit_id' => 'nullable'
        ]);
        $medical_test = MedicalTest::with('values')->findOrFail($validated['medical_test_id']);
        $medical_test_value_id  =   $medical_test->values->isNotEmpty() ? $medical_test->values->first()->id : null;

        // return $validated;
        $test = new UserMedicalTest();
        $test->date = Carbon::now()->format('Y-m-d');
        $test->type = $medical_test->name;
        $test->medical_test_id = $validated['medical_test_id'];
        $test->value = $validated['value'];
        $test->medical_test_value_id  = $medical_test_value_id;
        $test->user_id = $validated['user_id'];
        $test->unit_id = $validated['unit_id'];
        $test->laboratory_id = $validated['laboratory_id'];
        $test->save();

        return response()->json([
            'success' => true,
            'data' => $test
        ]);
    }

    // For left side under form 
    public function getUserMedicalTests(Request $request)
    {
        // $user_medical_tests = UserMedicalTest::where('user_id', auth()->id())->get();
        $user_id = $request->input('user_id');

        $user_medical_tests = UserMedicalTest::where('user_id', $user_id)
        ->latest()
        ->get();
        return view('admin.laboratory_tests.partials.medical_tests_list', compact('user_medical_tests'));
    }

    // show pdf file data not used
    public function showdatapdf(string $id)
    {
        //
        $pdf = LaboratoryTests::findOrFail($id);
        $filePath = Storage::path($pdf->file_path);

        // return response()->file($filePath);
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $text = $pdf->getText();
        $lines = explode("\n", $text); // Split text into lines
        $Hematology = []; // Array to store extracted data from current line
        $ClinicalChemistry = []; // Array to store extracted data from current line
        $allData = [];
        foreach ($lines as $line) {
            // Parse each line to extract data
            // Extract data from $line using appropriate parsing logic
            // For example, if each line contains comma-separated values:
            $parts = preg_split('/\s+/', $line, 2);
            if (count($parts) == 2) {
                $name = $parts[0]; // Assuming the first part is the name
                $value = $parts[1]; // Assuming the second part is the value
            }
            // Use regular expressions to extract string, number, and range
            // preg_match('/^(.*?)\s+([\d.]+)\s*(?:\S+\s+)?([\d.]+-\d+\.\d+)/', $value, $matches);
            preg_match('/^(.*?)\s+([\d.]+)\s*(?:%?\S+\s+)?([\d.]+-\d+(?:\.\d+)?)/', $value, $matches);


            // print_r(($matches));
            // echo '<br/>';

            if (count($matches) == 4) {
                $extractedString = trim($matches[1]);
                $extractedNumber = floatval($matches[2]);
                $range = $matches[3];

                // Check if the extracted string contains "WBC", "RBC", or "MCH"
                switch (true) {
                    case stripos($extractedString, 'WBC') !== false:
                        $Hematology += [
                            'wbc' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'RBC') !== false:
                        $Hematology += [
                            'rbc' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'MCH') !== false:
                        $Hematology += [
                            'mch' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'MCHC') !== false:
                        $Hematology += [
                            'mchc' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'RDW') !== false:
                        $Hematology += [
                            'rdw' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'MPV') !== false:
                        $Hematology += [
                            'mpv' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Neut #') !== false:
                        $Hematology += [
                            'neut#' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Lymph #') !== false:
                        $Hematology += [
                            'lymph#' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Baso #') !== false:
                        $Hematology += [
                            'baso#' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Mono #') !== false:
                        $Hematology += [
                            'mono#' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Eo #') !== false:
                        $Hematology += [
                            'eo#' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Neut %') !== false:
                        $Hematology += [
                            'neut%' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Lymph %') !== false:
                        $Hematology += [
                            'lymph%' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                    case stripos($extractedString, 'Baso %') !== false:
                        $Hematology += [
                            'baso%' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                    case stripos($extractedString, 'Mono %') !== false:
                        $Hematology += [
                            'mono%' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                    case stripos($extractedString, 'Eo %') !== false:
                        $Hematology += [
                            'eo%' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                        // Clinical Chemistry
                    case stripos($extractedString, 'Creatinine') !== false:
                        $ClinicalChemistry = [
                            'creatinine' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    case stripos($extractedString, 'Creatinine') !== false:
                        $ClinicalChemistry += [
                            'creatinine' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                    case stripos($extractedString, 'Cholesterol') !== false:
                        $ClinicalChemistry += [
                            'cholesterol' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;

                    case stripos($extractedString, 'Triglycerides') !== false:
                        $ClinicalChemistry += [
                            'triglycerides' => [
                                'name' => $extractedString,
                                'value' => $extractedNumber,
                                'range' => $range,
                            ]
                        ];
                        break;
                    default:
                        // Handle the case where none of the strings match
                        // You may add error handling or other logic here
                        break;
                }

                // Return or execute the query
                // echo "String: $extractedString";
                // echo '<br/>';
                // echo "Number: $extractedNumber";
                // echo '<br/>';
                // echo "range: $range";
            } else {
                // echo "No match found.";
            }

            // Insert $data into the database
            // YourModel::create($data);
            // print_r(($value));
            // echo '<br/>';
        }
        $allData = [
            'Hematology' => $Hematology,
            'ClinicalChemistry' => $ClinicalChemistry,
        ];
        echo '<pre>';
        print_r($allData);
        echo '<pre>';

        dd($lines);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $labtest = LaboratoryTests::findOrFail($id);

        // Delete the file from the storage
        if ($labtest->file_path) {
            Storage::delete($labtest->file_path);
        }

        // Delete the record from the database
        $labtest->delete();

        return redirect()->route('laboratory_tests.index')
            ->with('success', __('words.deleted'));
    }


    // save data from modal
    public function saveData(Request $request)
    {
        // Validate the request data if needed

        // Loop through the input data
        foreach ($request->all() as $key => $value) {
            // Check if the key is not '_token'
            if ($key !== '_token' && $key !== 'lab_id' && $key !== 'UserId') {
                // Check if the record exists with the given laboratory_id and type
                $medicalTest = UserMedicalTest::firstOrNew([
                    'laboratory_id' => $request->input('lab_id'),
                    'user_id' => $request->input('UserId'),
                    'type' => $key,
                ]);
                // Update or create the record with the given values
                $medicalTest->fill([
                    'value' => $value,
                    // 'user_id' => $request->input('UserId'),
                    'date' => Carbon::now(),
                ])->save();
            }
        }


        return response()->json(['success' => true,]);
    }

    // For Modal 
    public function getLaboratoryTestsData(Request $request)
    {
        // Fetch HTML content from the controller
        $LaboratoryTests = LaboratoryTests::where('id', $request->labId)->first();
        $userMedicalTests = UserMedicalTest::where('laboratory_id', $request->labId)->where('user_id', $LaboratoryTests->user_id)->get();

        $medical_tests = MedicalTest::whereNotNull('name')->get();

        // Check if user medical tests data exists
        if ($userMedicalTests->isNotEmpty()) {
            // Build HTML content
            $htmlContent = '<div class="input-wrapper" id="inputs-container">';
            foreach ($userMedicalTests as $userMedicalTest) {
                $htmlContent .= '
                <div class="row g-9 mb-3">
                    <div class="col-md-5 fv-row fv-plugins-icon-container">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">Choose a medical test:</span>
                            <span class="ms-1" data-bs-toggle="tooltip"
                                aria-label="Specify a target name for future usage and reference"
                                data-bs-original-title="Specify a target name for future usage and reference"
                                data-kt-initialized="1">
                                <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                            </span>
                        </label>
                        <select class="input-select">';
                foreach ($medical_tests as $medical_test) {
                    $htmlContent .= '<option value="' . $medical_test->name . '" ' . ($userMedicalTest->type == $medical_test->name ? 'selected' : '') . '>' . $medical_test->name . '</option>';
                }

                $htmlContent .= '</select>
                    
                    </div>
                    <div class="col-md-5 fv-row fv-plugins-icon-container">
                        <div class="additional-input">
                            <div class="d-flex flex-column mb-8 fv-row fv-plugins-icon-container">
                                <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                    <span class="required">' . $userMedicalTest->type . ' </span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        aria-label="Specify a target name for future usage and reference"
                                        data-bs-original-title="Specify a target name for future usage and reference"
                                        data-kt-initialized="1">
                                        <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                                    </span>
                                </label>
                                <input type="number" class="form-control form-control-solid"
                                    name="' . $userMedicalTest->type . '"
                                    value="' . $userMedicalTest->value . '"
                                    spellcheck="false" data-ms-editor="true">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-center align-content-center mt-4 p-0">
                    <span class="cancel-icon"><i class="fas fa-close"></i></span>
                </div>
                </div>';
            }
            $htmlContent .= '</div>';
        } else {
            $htmlContent = '<div>No user medical tests data found.</div>';
        }

        // Return HTML content
        return response()->json(['html' => $htmlContent]);
    }
}


    // public function show(string $id)
    // {
    //     try {
    //         // Retrieve the LaboratoryTests record along with its related files
    //         $pdf = LaboratoryTests::with('files')->findOrFail($id);

    //         // Access the file path from the related files
    //         $filePath = $pdf->files[0]->file_path; // Assuming there's only one file associated with the test

    //         $filePath = Storage::path($filePath);
    //         // Return the file path
    //         return response()->file($filePath);
    //     } catch (\Exception $e) {
    //         // Handle exceptions
    //     }
    // }