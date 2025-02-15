<?php

namespace App\Http\Controllers\admin;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class AdminOfferController extends Controller
{
    // This controller to edit , create , update , delete from the menu

    use ApiResponseTrait;

    public function store(Request $request){
        try{
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'oldprice' => 'required|numeric|min:0',
            'newprice' => 'required|numeric|min:0',
            'category' => 'required|in:Eastern_food,Western_food,Desserts,Juices', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName(); 
            $image->storeAs('public/images', $imageName); 
            $validated['image_name'] = $imageName; 
        }
    
        $offer = Offer::create($validated);
        return $this->createdResponse($offer);
    }catch (\Illuminate\Validation\ValidationException $e) {
        return $this->validationErrorResponse($e->errors(), "Validation errors occurred");
    } catch (\Exception $e) {
        return $this->serverErrorResponse($e->getMessage());
    }
    }

    public function update(Request $request,$id){
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'oldprice' => 'required|numeric|min:0',
                'newprice' => 'required|numeric|min:0',
                'category' => 'required|in:Eastern_food,Western_food,Desserts,Juices', 
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            ]);
            $offer = Offer::find($id);
            if (!$offer) {
                return $this->notFoundResponse();
            }

            if ($request->hasFile('image')) {
                if ($offer->image_name && Storage::exists('public/images/' . $offer->image_name)) {
                    Storage::delete('public/images' . $offer->image_name);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName(); 
                $image->storeAs('public/images', $imageName); 
                $validated['image_name'] = $imageName; 
            }

            $offer->update($validated);
            return $this->updatedResponse($offer);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), "Validation errors occurred");
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
    public function edit($id): JsonResponse
    {
        $offer = Offer::find($id);
        if (!$offer) {
            return $this->notFoundResponse();
        }
        return $this->apiResponse("Ok", "Data retrieved successfully", $offer);
    }
    public function destroy($id): JsonResponse
    {
        $offer = Offer::find($id);
        if (!$offer) {
            return $this->notFoundResponse();
        }

        if ($offer->image_name && Storage::exists('public/images/' . $offer->image_name)) {
            Storage::delete('public/images/' . $offer->image_name);
        }

        $offer->delete();

        return $this->deletedResponse();
    }


}
