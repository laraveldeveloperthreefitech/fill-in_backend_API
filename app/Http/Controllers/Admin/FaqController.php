<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Facades\Log;
use Exception;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = Faq::search($request)->latest()->paginate(10);
            return view('admin.faq.index', compact('data'));
        } catch (Exception $e) {
            Log::error('FAQ Index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while fetching FAQs.');
        }
    }
    

    public function create()
    {
        try {
            return view('admin.faq.edit');
        } catch (Exception $e) {
            Log::error('FAQ Create Page Error: ' . $e->getMessage());
            return redirect()->route('admin.faq.index')->with('error', 'Failed to open create page.');
        }
    }

    // public function store(Request $request)
    // {

    //     // dd($request->all());
    //     try {
    //         $request->validate([
    //             'question' => 'required|string|max:255',
    //             'answer' => 'required|string',
    //             'role' => 'required|in:0,1',
    //         ]);

    //         Faq::create($request->only('question', 'answer', 'role','status'));

    //         return redirect()->route('admin.faq.index')->with('success', 'FAQ created successfully.');
    //     } catch (Exception $e) {
    //         Log::error('FAQ Store Error: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to create FAQ.')->withInput();
    //     }
    // }

    public function edit($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            return view('admin.faq.edit', compact('faq'));
        } catch (Exception $e) {
            Log::error('FAQ Edit Error: ' . $e->getMessage());
            return redirect()->route('admin.faq.index')->with('error', 'Failed to load FAQ for editing.');
        }
    }

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $faq = Faq::findOrFail($id);

    //         $request->validate([
    //             'question' => 'required|string',
    //             'answer' => 'nullable|string',
    //             'role' => 'required|in:0,1',
    //         ]);

    //         $faq->update($request->only('question', 'answer', 'role','status'));

    //         return redirect()->route('admin.faq.index')->with('success', 'FAQ updated successfully!');
    //     } catch (Exception $e) {
    //         Log::error('FAQ Update Error: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to update FAQ.')->withInput();
    //     }
    // }

    public function storeOrUpdate(Request $request, $id = null)
{
    try {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'role' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $faq = Faq::updateOrCreate(
            ['id' => $id], // condition
            $request->only('question', 'answer', 'role', 'status') // fields to update/create
        );

        $message = $id ? 'FAQ updated successfully.' : 'FAQ created successfully.';

        return redirect()->route('admin.faq.index')->with('success', $message);
    } catch (Exception $e) {
        Log::error('FAQ StoreOrUpdate Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to save FAQ.')->withInput();
    }
}


    public function destroy(Request $request)
    {
        try {
            $deleted = Faq::where('id', $request->id)->delete();

            return response()->json(['status' => $deleted]);
        } catch (Exception $e) {
            Log::error('FAQ Delete Error: ' . $e->getMessage());
            return response()->json(['status' => false]);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $data = Faq::find($request->id);
            if ($data) {
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
                return redirect()->route('admin.faq.index')->with('success', 'Status changed successfully.');
            }
            return redirect()->route('admin.faq.index')->with('error', 'FAQ not found.');
        } catch (Exception $e) {
            Log::error('FAQ Status Change Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function deActiveAll(Request $request)
    {
        try {
            $ids = $request->ids ?? [];

            if (count($ids) === 0) {
                return response()->json(['status' => false]);
            }

            $updated = Faq::whereIn('id', $ids)->update(['status' => 0]);

            return response()->json(['status' => $updated ? true : false]);
        } catch (Exception $e) {
            Log::error('FAQ Bulk Deactivate Error: ' . $e->getMessage());
            return response()->json(['status' => false]);
        }
    }
}
