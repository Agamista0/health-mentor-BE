<?php

namespace App\Http\Controllers\Dashboard\LaboratoryTests;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTests;
use Illuminate\Http\Request;
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
        return view('admin.laboratory_tests.index', compact('LaboratoryTests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
        $allData =[];
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
        $allData =[
            'Hematology'=>$Hematology,
            'ClinicalChemistry'=>$ClinicalChemistry,
        ];
        echo '<pre>';
        print_r($allData);
        echo '<pre>';

        dd($lines);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
