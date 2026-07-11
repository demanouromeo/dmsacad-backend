<?php

namespace App\Http\Controllers;

use App\Models\Products2;
use Illuminate\Http\Request;

class Products2Controller extends Controller
{
    /*
    public function upload(Request $request){
        $connection = $request->input("connection");
        echo $connection;
        $products = Products2::all();
        return response()->json($products, 200);
    }*/
    public function upload(Request $request)
    {
        $request->validate([
            'name' => 'required', 
            'connection' => 'required', 
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        //$name = $request->input('name');
        $connection = $request->input('connection');
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path("images/$connection"), $imageName);
        $product = new Products2();
        $product->name = $request->name; 
        $product->image_path = "images/$connection/" . $imageName;
        $product->save();
        echo "Product saved";
        //return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }
}
