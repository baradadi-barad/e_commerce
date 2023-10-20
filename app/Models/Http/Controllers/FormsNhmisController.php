<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Nutrition;
use App\MalariaPreventation;
use App\Imci;
use App\FamilyPlanning;
use App\Referrals;
use App\Immunization;
use App\CommunicableDiseases;
use Session;
use Auth;

class FormsNhmisController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        deleteNotification();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }
    public function nutrition()
    {
        return view('forms.nhmis.nutrition');
    }
    public function viewNutrition()
    {
        $userId = Auth::user()->id;   
        $nutrition = Nutrition::orderBy('id','desc')->where('added_by',$userId)->paginate(10);
        return view('forms.nhmis.nutrition.view', ['nutrition' => $nutrition]);
    }
    public function addNutrition(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $nutrition = new Nutrition;
        $nutrition->added_by = $userId;
        $nutrition->children_0to59_mwt = $postData['children_0to59_mwt'] ;
        $nutrition->children_0to59_mwbbl = $postData['children_0to59_mwbbl'] ;
        $nutrition->children_0to6_rbebf = $postData['children_0to6_rbebf'] ;
        $nutrition->children_6to11_mgva = $postData['children_6to11_mgva'] ;
        $nutrition->children_12to59_mgva = $postData['children_12to59_mgva'] ;
        $nutrition->children_12to59_mgdm = $postData['children_12to59_mgdm'] ;
        $nutrition->children_lt5y_otp_sc = $postData['children_lt5y_otp_sc'] ;
        $nutrition->children_lt5y_discharged = $postData['children_lt5y_discharged'] ;
        $nutrition->children_admitted_cmam_program = $postData['children_admitted_cmam_program'] ;
        $nutrition->children_defaulted_cmam_program = $postData['children_defaulted_cmam_program'] ;
        $nutrition->save();


        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('forms.nhmis.nutrition');
    }
    
    public function malariaPreventation()
    {
        return view('forms.nhmis.malaria-preventation');
    }
    public function addMalariaPreventation(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
         
        $data = new MalariaPreventation;
        $data->added_by = $userId ;
        $data->children_u5y_received_llin = $postData['children_u5y_received_llin'] ;
        $data->save();

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('forms.nhmis.malaria-preventation');
    }
    public function imci()
    {
        return view('forms.nhmis.imci');
    }
     public function addImci(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new Imci;
        $data->added_by = $userId;
        $data->diarrhoea_nc_lt5y = $postData['diarrhoea_nc_lt5y'] ;
        $data->diarrhoea_nc_lt5y_gorp = $postData['diarrhoea_nc_lt5y_gorp'] ;
        $data->diarrhoea_nc_lt5y_gozs = $postData['diarrhoea_nc_lt5y_gozs'] ;
        $data->pneumonia_nc_lt5y = $postData['pneumonia_nc_lt5y'] ;
        $data->pneumonia_nc_lt5y_ga = $postData['pneumonia_nc_lt5y_ga'] ;
        $data->measles_nc_lt5y = $postData['measles_nc_lt5y'] ;
        $data->save();

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('forms.nhmis.imci');
    }
    public function familyPlanning()
    {
        return view('forms.nhmis.family-planning');
    }
    public function addFamilyPlanning(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new FamilyPlanning;
        $data->added_by = $userId;
        $data->clients_counselled = $postData['clients_counselled'] ;
        $data->new_fp_acceptors = $postData['new_fp_acceptors'] ;
        $data->fp_ca_hct_services = $postData['fp_ca_hct_services'] ;
        $data->ir_fp_services_from_hct = $postData['ir_fp_services_from_hct'] ;
        $data->ir_fp_services_from_art = $postData['ir_fp_services_from_art'] ;
        $data->females_aged_15to49y_mc = $postData['females_aged_15to49y_mc'] ;
        $data->persons_given_oral_pills = $postData['persons_given_oral_pills'] ;
        $data->oral_pill_cycle_dispensed = $postData['oral_pill_cycle_dispensed'] ;
        $data->injectables_given = $postData['injectables_given'] ;
        $data->iucd_inserted = $postData['iucd_inserted'] ;
        $data->implants_inserted = $postData['implants_inserted'] ;
        $data->sterilization = $postData['sterilization'] ;
        $data->male_condoms_distributed = $postData['male_condoms_distributed'] ;
        $data->female_condoms_distributed = $postData['female_condoms_distributed'] ;
        $data->ir_fp_services_from_pmtct = $postData['ir_fp_services_from_pmtct'] ;
        $data->save();

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('forms.nhmis.family-planning');
    }
    public function Referrals()
    {
        return view('forms.nhmis.referrals');
    }
    public function addReferrals(Request $request)
    {
        $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
        $data = new Referrals;
        $data->added_by = $userId;
        $data->referral_in = $postData['referral_in'] ;
        $data->referral_out = $postData['referral_out'] ;
        $data->mcr_further_treatment = $postData['mcr_further_treatment'] ;
        $data->mcr_adverse_drug_reaction = $postData['mcr_adverse_drug_reaction'] ;
        $data->wro_pregnancy_related_complications = $postData['wro_pregnancy_related_complications'] ;
        $data->wsar_obstetric_fistula = $postData['wsar_obstetric_fistula'] ;
        $data->save();

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('forms.nhmis.referrals');
    }
    public function immunization(Request $request){
       return view('forms.nhmis.Immunization.Immunization');
    }
    public function immunizationDisplay(Request $request){
       return view('forms.nhmis.Immunization.Immunization');
    }
    public function nonCommunicableDiseases(Request $request){
       return view('forms.nhmis.non-communicableDiseases');
    }
    
     public function immunizationInsert(Request $request) {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $immunization = new Immunization;
        $immunization->added_by = $userId;
        $immunization->opv_0_birth = $postData['opv_0_birth'] ;
        $immunization->hep_b_0_birth = $postData['hep_b_0_birth'] ;
        $immunization->bcg = $postData['bcg'] ;
        $immunization->opv_1 = $postData['opv_1'] ;
        $immunization->hep_b_1 = $postData['hep_b_1'] ;
        $immunization->penta_1 = $postData['penta_1'] ;
        $immunization->dpt_1 = $postData['dpt_1'] ;
        $immunization->pcv_1 = $postData['pcv_1'] ;
        $immunization->opv_2 = $postData['opv_2'] ;
        $immunization->hep_b_2 = $postData['hep_b_2'] ;
        $immunization->penta_2 = $postData['penta_2'] ;
        $immunization->dpt_2 = $postData['dpt_2'] ;
        $immunization->pcv_2 = $postData['pcv_2'] ;
        $immunization->opv_3 = $postData['opv_3'] ;
        $immunization->penta_3 = $postData['penta_3'] ;
        $immunization->dpt_3 = $postData['dpt_3'] ;
        $immunization->pcv_3 = $postData['pcv_3'] ;
        $immunization->measles_1 = $postData['measles_1'] ;
        $immunization->fully_immunized_l1_year = $postData['fully_immunized_l1_year'] ;
        $immunization->yellow_fever = $postData['yellow_fever'] ;
        $immunization->measles_2 = $postData['measles_2'] ;
        $immunization->conjugate_a_csm = $postData['conjugate_a_csm'] ;
        $immunization->save();
            //store status message
            Session::flash('successMessage', 'Record added successfully!');
            return redirect()->route('forms.nhmis.immunization');
        
    }
    
    
     public function nonCommunicableDiseasesInsert(Request $request) {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $communicablediseases = new CommunicableDiseases;
        $communicablediseases->added_by = $userId;
        $communicablediseases->coronary_heart_disease_nc = $postData['coronary_heart_disease_nc'] ;
        $communicablediseases->diabetes_mellitus_nc = $postData['diabetes_mellitus_nc'] ;
        $communicablediseases->hypertension_nc = $postData['hypertension_nc'] ;
        $communicablediseases->sickle_cell_disease_nc = $postData['sickle_cell_disease_nc'] ;
        $communicablediseases->road_traffic_accident_nc = $postData['road_traffic_accident_nc'] ;
        $communicablediseases->home_accident_nc = $postData['home_accident_nc'] ;
        $communicablediseases->snake_bites_nc = $postData['snake_bites_nc'] ;
        $communicablediseases->asthma_nc = $postData['asthma_nc'] ;
        $communicablediseases->athritis_nc = $postData['athritis_nc'] ;
        
        $communicablediseases->save();
            //store status message
            Session::flash('successMessage', 'Record added successfully!');
            return redirect()->route('forms.nhmis.non-communicableDiseases');
        
    }
}
