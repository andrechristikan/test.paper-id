<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\FinanceAccountRequest;
use App\Models\FinanceAccount;
use Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class FinanceAccountController extends Controller
{
    
    public function index(Request $request)
    {
        $page = ( $request->query('page') && is_numeric($request->query('page')) ) ? (int) $request->query('page') : 1;
        $per_page = ( $request->query('per_page') && is_numeric($request->query('per_page')) ) ? (int) $request->query('per_page') : 10;
        $off_set= ($page - 1) * $per_page;


        $search = $request->query('search');
        $sort = $request->query('sort');
        if($sort && str_contains($sort, '@')){
            $sort = explode('@', $sort);

            if($sort[1] != 'asc' && $sort[1] != 'desc'){
                $sort[1] = 'asc';
            }
        }

        $finance_account = FinanceAccount::getByUserId();
        
        if($search){
            $finance_account->search($search);
        }

        $count = $finance_account->count();
        $total_page = floor($count / $per_page) ?: 1;

        if(is_array($sort) && count($sort) > 1){
            ($sort[1] == 'desc') 
                ? 
                $finance_account->orderByDesc($sort[0]) 
                :
                $finance_account->orderBy($sort[0]);
        }

        $finance_account = $finance_account->offset($off_set)->limit($per_page)->get();
        return response()
            ->json([
                'status_code' => 200,
                'message' => trans('finance-account.get-all'),
                'page'=> $page,
                'per_page'=> $per_page,
                'total_page'=> $total_page,
                'count'=> $count,
                'data' => $finance_account
            ], 200);
    }


    public function store(FinanceAccountRequest $request)
    {
    
        $user = Auth::guard()->user();
        $request_body = $request->only(['name']);
        $finance_account = new FinanceAccount($request_body);
        $finance_account->user_id = $user->id;
        if(!$finance_account->save()){
            throw new HttpException(trans('http.internal-server-error'));
        }

        return response()
            ->json([
                'status_code' => 201,
                'message' => trans('finance-account.store'),
                'data' => [
                    'id' => $finance_account->id
                ]
            ], 201);
    }


    public function show($id)
    {
        $finance_account = FinanceAccount::getOneByUserIdAndId($id)->first();
        if(!$finance_account){
            throw new NotFoundHttpException(trans('http.not-found'));
        }

        return response()
            ->json([
                'status' => 'ok',
                'message' => trans('finance-account.get-by-id'),
                'data' => $finance_account
            ], 200);
    }


    public function update(FinanceAccountRequest $request, $id)
    {
        $request_body = $request->only(['name']);
        $finance_account = FinanceAccount::getOneByUserIdAndId($id)->first();
        if(!$finance_account){
            throw new NotFoundHttpException(trans('http.not-found'));
        }
        
        $finance_account->name = $request_body['name'];
        if(!$finance_account->save()){
            throw new HttpException(trans('http.internal-server-error'));
        }

        return response()
            ->json([
                'status' => 'ok',
                'message' => trans('finance-account.update-by-id'),
                'data' => [
                    'id' => $finance_account->id
                ]
            ], 200);
    }

    
    public function destroy($id)
    {
        $finance_account = FinanceAccount::getOneByUserIdAndId($id)->first();
        if(!$finance_account){
            throw new NotFoundHttpException(trans('http.not-found'));
        }

        if(!$finance_account->delete()){
            throw new HttpException(trans('http.internal-server-error'));
        }

        return response()
            ->json([
                'status' => 'ok',
                'message' => trans('finance-account.destroy-by-id')
            ], 200);
    }
}
