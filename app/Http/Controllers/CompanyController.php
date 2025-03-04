<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
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

  public function show(Request $req)
  {
    $company = Company::find($req->user->company_id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    return response()->json($company);
  }

  public function update(Request $req)
  {
    $company = Company::find($req->user->company_id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->update($req->all());
    return response()->json($company);
  }

  public function destroy(Request $req)
  {
    $company = Company::find($req->user->company_id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->delete();
    return response()->json(['message' => 'Company deleted successfully']);
  }
}
