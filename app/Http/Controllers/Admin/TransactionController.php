<?php

namespace App\Http\Controllers\Admin;

use App\Product;
use App\Transaction;
use App\TransactionDetail;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()-> ajax())
        {
            $query = Transaction::with('user');
            // ->withTrashed(); // untuk mengambalikan data menggunakan softdeletes;
            // $query = transaction::with(['user']);

            return DataTables::of($query)
                ->addColumn('action', function($item) {
                    return '
                        <div class="btn-group">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle mr-1 mb-1"
                                    type="button"
                                    data-toggle="dropdown">
                                    Aksi
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="' . route('transaction.edit', $item->id) .  '">
                                    Sunting
                                    </a>
                                    <form action="' . route('transaction.destroy', $item->id) .'" method="POST">
                                        ' . method_field('delete') . csrf_field() . '
                                        <button type="submit" class="dropdown-item text-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make();
        }
        return view('pages.admin.transaction.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Transaction::findOrFail($id);
        $TD = TransactionDetail::where(['transactions_id' => $id])->first();

        return view('pages.admin.transaction.edit' , [
            'item' => $item,
            'td' => $TD
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Update Transaction
        $data = $request->all();
        dd($data['resi']);
        $item = Transaction::findOrFail($id);
        $item->update($data);


        if ($data['transaction_status'] == "SHIPPING"){
            $TransactonDetails = TransactionDetail::where(['transactions_id' => $id])->get();
            foreach($TransactonDetails as $TD){
                // Update Resi
                $UpdateResi = TransactionDetail::findOrFail($TD->id);
                $UpdateResi->update([
                    'resi' => $data['resi']
                ]);

                // Update stock
                $item = Product::findOrFail($TD->products_id);
                $item->update([
                    'stock' => $item->stock - $TD->quantity
                ]);
            }
        }

        return redirect()->route('transaction.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = transaction::findOrFail($id);
        $item->delete();

        return redirect()->route('transaction.index');
    }
}
