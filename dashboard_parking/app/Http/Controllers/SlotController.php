<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlotController extends Controller
{
    // Method to display the dashboard with data directly from the database
    public function showDashboard()
    {
        // Fetch the latest status for each slot from the database (Slot 1, Slot 2, Slot 3)
        $slot1 = ParkingSlot::where('slot_number', 1)->latest('date')->first();
        $slot2 = ParkingSlot::where('slot_number', 2)->latest('date')->first();
        $slot3 = ParkingSlot::where('slot_number', 3)->latest('date')->first();

        // Get unique days for the chart labels
        $days = $this->getDaysFromSlots();

        // Get slot status data (for the chart)
        $slotData = [
            'slot1' => $this->getSlotStatus($slot1), // Slot 1
            'slot2' => $this->getSlotStatus($slot2), // Slot 2
            'slot3' => $this->getSlotStatus($slot3), // Slot 3
        ];

        // Get the most recent status for each slot (for the cards)
        $latestStatus = [
            'slot1' => $slot1 ? $slot1->status : null,
            'slot2' => $slot2 ? $slot2->status : null,
            'slot3' => $slot3 ? $slot3->status : null,
        ];

        // Pass the data to the Blade view
        return view('dashboard', compact('days', 'slotData', 'latestStatus'));
    }

    // Helper function to get unique days from slots data
    private function getDaysFromSlots()
    {
        // Get unique days from the database, ordered correctly
        $days = ParkingSlot::select(DB::raw('DATE(date) as date_only'))
            ->groupBy('date_only')
            ->orderBy('date_only')
            ->pluck('date_only');

        // Format days into "Hari 1", "Hari 2", etc.
        return $days->map(function ($date, $index) {
            return 'Hari ' . ($index + 1);
        });
    }

    // Helper function to get the status for each slot based on slot number
    private function getSlotStatus($slot)
    {
        // If slot exists, return the status, otherwise return null
        return $slot ? $slot->status : null;
    }

    // Method to get the status of each slot per day (for chart)
    public function getSlots(Request $request)
    {
        $filter = $request->query('status', 'isi'); // 'isi' or 'kosong'
        $statusValue = ($filter == 'isi') ? 1 : 0;

        // Get all unique dates from the database, ordered correctly
        $days = ParkingSlot::select(DB::raw('DATE(date) as date_only'))
            ->groupBy('date_only')
            ->orderBy('date_only')
            ->pluck('date_only');

        $slotData = [
            'slot1' => [],
            'slot2' => [],
            'slot3' => [],
        ];

        // Loop through the days to fetch the data
        foreach ($days as $day) {
            // Format the day to 'Y-m-d' for consistency
            $formattedDay = date('Y-m-d', strtotime($day));
            
            // For slot 1
            $slot1 = ParkingSlot::where('date', $formattedDay)
                ->where('slot_number', 1)
                ->first();
            $slotData['slot1'][] = ($slot1 && $slot1->status == $statusValue) ? 24 : 0;

            // For slot 2
            $slot2 = ParkingSlot::where('date', $formattedDay)
                ->where('slot_number', 2)
                ->first();
            $slotData['slot2'][] = ($slot2 && $slot2->status == $statusValue) ? 24 : 0;

            // For slot 3
            $slot3 = ParkingSlot::where('date', $formattedDay)
                ->where('slot_number', 3)
                ->first();
            $slotData['slot3'][] = ($slot3 && $slot3->status == $statusValue) ? 24 : 0;
        }

        // Format the days into "Hari 1", "Hari 2", etc.
        $formattedDays = $days->map(function ($date, $index) {
            return 'Hari ' . ($index + 1);
        });

        return response()->json([
            'days' => $formattedDays,
            'slots' => $slotData
        ]);
    }

    // Method to receive POST data from ESP32 and save to the database
    public function store(Request $request)
    {
        // Validate input data from ESP32 (must be boolean)
        $request->validate([
            'slot1' => 'required|boolean',
            'slot2' => 'required|boolean',
            'slot3' => 'required|boolean',
        ]);

        $currentDate = now()->format('Y-m-d');

        // Update or create for each slot based on ESP data
        $this->updateOrCreateSlot(1, $request->input('slot1'), $currentDate);
        $this->updateOrCreateSlot(2, $request->input('slot2'), $currentDate);
        $this->updateOrCreateSlot(3, $request->input('slot3'), $currentDate);

        return response()->json(['message' => 'Data slot parkir berhasil disimpan'], 200);
    }

    // Helper method to update or create slot
    private function updateOrCreateSlot($slotNumber, $status, $date)
    {
        ParkingSlot::updateOrCreate(
            [
                'slot_number' => $slotNumber,
                'date' => $date
            ],
            [
                'status' => $status
            ]
        );
    }

    // Method to get the latest status of each slot
    public function getLatestStatus()
    {
        // Get the latest status for each slot
        $slot1 = ParkingSlot::where('slot_number', 1)
            ->latest('date')
            ->first();
        
        $slot2 = ParkingSlot::where('slot_number', 2)
            ->latest('date')
            ->first();
        
        $slot3 = ParkingSlot::where('slot_number', 3)
            ->latest('date')
            ->first();

        // Return the latest status
        return response()->json([ 
            'currentStatus' => [
                $slot1 ? $slot1->status : null,  // Slot 1 status
                $slot2 ? $slot2->status : null,  // Slot 2 status
                $slot3 ? $slot3->status : null   // Slot 3 status
            ]
        ]);
    }
}
