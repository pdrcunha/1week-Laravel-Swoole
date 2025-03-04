<?php

namespace App\Http\Controllers\AdminManager;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyManagerController extends Controller
{
  public function index()
  {
    return response()->json(Company::all());
  }

  public function store(Request $req)
  {
    $company = Company::create($req->all());
    return response()->json($company, 201);
  }

  public function show($id)
  {
    $company = Company::find($id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    return response()->json($company);
  }

  public function update(Request $req)
  {
    $data = $req->all();
    $company = Company::find($data['id']);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->update($req->all());
    return response()->json($company);
  }

  public function destroy($id)
  {
    $company = Company::find($id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->delete();
    return response()->json(['message' => 'Company deleted successfully']);
  }
}
