<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\CommunicableDisease;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Hospitals;
use App\User;
use DB;

class CommunicableDiseaseController extends Controller
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
   
    public function add(Request $request){
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
       return view('communicable-disease.add',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
     public function view()
    {
        $userId = Auth::user()->id;       
        $data = CommunicableDisease::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->paginate(10);
       
        return view('communicable-disease.display', ['data' => $data]);
    }
    public function display($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = CommunicableDisease::find($id);
        return view('communicable-disease.display', ['data' => $data],['years'=>$years]);
    }
     public function index()
    {
        $userId = Auth::user()->id;       
        $data = CommunicableDisease::orderBy('id','desc')->where('added_by',$userId)->with('addedBy')->get();
        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();
       
        return view('communicable-disease.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by]);
    }
    
     public function delete($id)
    {
        
        CommunicableDisease::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In Communicable Disease';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'communicable-disease';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('communicable-disease');
    }
    public function edit($id)
    {
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
        $data = CommunicableDisease::find($id);
        return view('communicable-disease.edit', ['data' => $data],['years'=>$years]);
    }
    public function insert(Request $request){
        
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();

        $data = new CommunicableDisease;
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->year = $postData['year'] ;
        $data->new_malaria_cases_male = $postData['new_malaria_cases_male'];
        $data->new_malaria_cases_female = $postData['new_malaria_cases_female'];
        $data->new_malaria_cases_total = $postData['new_malaria_cases_total'];
        $data->clinic_tested_malaria_male = $postData['clinic_tested_malaria_male'];
        $data->clinic_tested_malaria_female = $postData['clinic_tested_malaria_female'];
        $data->clinic_tested_malaria_total = $postData['clinic_tested_malaria_total'];
        $data->malaria_cases_treated_with_act_male = $postData['malaria_cases_treated_with_act_male'];
        $data->malaria_cases_treated_with_act_female = $postData['malaria_cases_treated_with_act_female'];
        $data->malaria_cases_treated_with_act_total = $postData['malaria_cases_treated_with_act_total'];
        $data->malaria_in_pregnancy_male = $postData['malaria_in_pregnancy_male'];
        $data->malaria_in_pregnancy_female = $postData['malaria_in_pregnancy_female'];
        $data->malaria_in_pregnancy_total = $postData['malaria_in_pregnancy_total'];
        $data->drug_resistance_malaria_cases_male = $postData['drug_resistance_malaria_cases_male'];
        $data->drug_resistance_malaria_cases_female = $postData['drug_resistance_malaria_cases_female'];
        $data->drug_resistance_malaria_cases_total = $postData['drug_resistance_malaria_cases_total'];
        $data->malaria_severe_male = $postData['malaria_severe_male'];
        $data->malaria_severe_female = $postData['malaria_severe_female'];
        $data->malaria_severe_total = $postData['malaria_severe_total'];
        $data->number_of_new_hiv_cases_male = $postData['number_of_new_hiv_cases_male'];
        $data->number_of_new_hiv_cases_female = $postData['number_of_new_hiv_cases_female'];
        $data->number_of_new_hiv_cases_total = $postData['number_of_new_hiv_cases_total'];
        $data->number_of_co_factor_cases_tb_hiv_male = $postData['number_of_co_factor_cases_tb_hiv_male'];
        $data->number_of_co_factor_cases_tb_hiv_female = $postData['number_of_co_factor_cases_tb_hiv_female'];
        $data->number_of_co_factor_cases_tb_hiv_total = $postData['number_of_co_factor_cases_tb_hiv_total'];
        $data->number_of_hiv_persons_on_art_male = $postData['number_of_hiv_persons_on_art_male'];
        $data->number_of_hiv_persons_on_art_female = $postData['number_of_hiv_persons_on_art_female'];
        $data->number_of_hiv_persons_on_art_total = $postData['number_of_hiv_persons_on_art_total'];
        $data->number_of_dropouts_male = $postData['number_of_dropouts_male'];
        $data->number_of_dropouts_female = $postData['number_of_dropouts_female'];
        $data->number_of_dropouts_total = $postData['number_of_dropouts_total'];
        $data->death_complication_related_to_hiv_aids_male = $postData['death_complication_related_to_hiv_aids_male'];
        $data->death_complication_related_to_hiv_aids_female = $postData['death_complication_related_to_hiv_aids_female'];
        $data->death_complication_related_to_hiv_aids_total = $postData['death_complication_related_to_hiv_aids_total'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_male = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_male'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_female = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_female'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_total = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_total'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_male = $postData['new_case_of_severe_actute_respiratory_illness_sari_male'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_female = $postData['new_case_of_severe_actute_respiratory_illness_sari_female'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_total = $postData['new_case_of_severe_actute_respiratory_illness_sari_total'];
        $data->new_case_of_neonatal_tetanus_male = $postData['new_case_of_neonatal_tetanus_male'];
        $data->new_case_of_neonatal_tetanus_female = $postData['new_case_of_neonatal_tetanus_female'];
        $data->new_case_of_neonatal_tetanus_total = $postData['new_case_of_neonatal_tetanus_total'];
        $data->new_case_of_measles_male = $postData['new_case_of_measles_male'];
        $data->new_case_of_measles_female = $postData['new_case_of_measles_female'];
        $data->new_case_of_measles_total = $postData['new_case_of_measles_total'];
        $data->new_case_of_onchcerciasis_male = $postData['new_case_of_onchcerciasis_male'];
        $data->new_case_of_onchcerciasis_female = $postData['new_case_of_onchcerciasis_female'];
        $data->new_case_of_onchcerciasis_total = $postData['new_case_of_onchcerciasis_total'];
        $data->new_case_of_poliomylitis_male = $postData['new_case_of_poliomylitis_male'];
        $data->new_case_of_poliomylitis_female = $postData['new_case_of_poliomylitis_female'];
        $data->new_case_of_poliomylitis_total = $postData['new_case_of_poliomylitis_total'];
        $data->new_case_of_rabies_human_male = $postData['new_case_of_rabies_human_male'];
        $data->new_case_of_rabies_human_female = $postData['new_case_of_rabies_human_female'];
        $data->new_case_of_rabies_human_total = $postData['new_case_of_rabies_human_total'];
        $data->new_case_of_smallpox_male = $postData['new_case_of_smallpox_male'];
        $data->new_case_of_smallpox_female = $postData['new_case_of_smallpox_female'];
        $data->new_case_of_smallpox_total = $postData['new_case_of_smallpox_total'];
        $data->new_case_of_sexually_transmitted_infection_stis_male = $postData['new_case_of_sexually_transmitted_infection_stis_male'];
        $data->new_case_of_sexually_transmitted_infection_stis_female = $postData['new_case_of_sexually_transmitted_infection_stis_female'];
        $data->new_case_of_sexually_transmitted_infection_stis_total = $postData['new_case_of_sexually_transmitted_infection_stis_total'];
        $data->new_case_of_yellow_fever_male = $postData['new_case_of_yellow_fever_male'];
        $data->new_case_of_yellow_fever_female = $postData['new_case_of_yellow_fever_female'];
        $data->new_case_of_yellow_fever_total = $postData['new_case_of_yellow_fever_total'];
        $data->new_case_of_finding_of_tuberculosis_male = $postData['new_case_of_finding_of_tuberculosis_male'];
        $data->new_case_of_finding_of_tuberculosis_female = $postData['new_case_of_finding_of_tuberculosis_female'];
        $data->new_case_of_finding_of_tuberculosis_total = $postData['new_case_of_finding_of_tuberculosis_total'];
        $data->tb_hiv_co_infection_case_male = $postData['tb_hiv_co_infection_case_male'];
        $data->tb_hiv_co_infection_case_female = $postData['tb_hiv_co_infection_case_female'];
        $data->tb_hiv_co_infection_case_total = $postData['tb_hiv_co_infection_case_total'];
        $data->tb_patient_on_art_male = $postData['tb_patient_on_art_male'];
        $data->tb_patient_on_art_female = $postData['tb_patient_on_art_female'];
        $data->tb_patient_on_art_total = $postData['tb_patient_on_art_total'];
        $data->multiple_drug_reaction_tb_cases_male = $postData['multiple_drug_reaction_tb_cases_male'];
        $data->multiple_drug_reaction_tb_cases_female = $postData['multiple_drug_reaction_tb_cases_female'];
        $data->multiple_drug_reaction_tb_cases_total = $postData['multiple_drug_reaction_tb_cases_total'];
        $data->number_of_new_cases_covid_19_male = $postData['number_of_new_cases_covid_19_male'];
        $data->number_of_new_cases_covid_19_female = $postData['number_of_new_cases_covid_19_female'];
        $data->number_of_new_cases_covid_19_total = $postData['number_of_new_cases_covid_19_total'];
        $data->clinic_tested_covid_19_male = $postData['clinic_tested_covid_19_male'];
        $data->clinic_tested_covid_19_female = $postData['clinic_tested_covid_19_female'];
        $data->clinic_tested_covid_19_total = $postData['clinic_tested_covid_19_total'];
        $data->cases_reported_treated_covid_19_male = $postData['cases_reported_treated_covid_19_male'];
        $data->cases_reported_treated_covid_19_female = $postData['cases_reported_treated_covid_19_female'];
        $data->cases_reported_treated_covid_19_total = $postData['cases_reported_treated_covid_19_total'];
        $data->drug_resistenace_covid_19_cases_male = $postData['drug_resistenace_covid_19_cases_male'];
        $data->drug_resistenace_covid_19_cases_female = $postData['drug_resistenace_covid_19_cases_female'];
        $data->drug_resistenace_covid_19_cases_total = $postData['drug_resistenace_covid_19_cases_total'];
        $data->number_of_new_faces_lassa_fever_male = $postData['number_of_new_faces_lassa_fever_male'];
        $data->number_of_new_faces_lassa_fever_female = $postData['number_of_new_faces_lassa_fever_female'];
        $data->number_of_new_faces_lassa_fever_total = $postData['number_of_new_faces_lassa_fever_total'];
        $data->clinic_tested_lassa_fever_male = $postData['clinic_tested_lassa_fever_male'];
        $data->clinic_tested_lassa_fever_female = $postData['clinic_tested_lassa_fever_female'];
        $data->clinic_tested_lassa_fever_total = $postData['clinic_tested_lassa_fever_total'];
        $data->lassa_fever_cases_reported_treated_male = $postData['lassa_fever_cases_reported_treated_male'];
        $data->lassa_fever_cases_reported_treated_female = $postData['lassa_fever_cases_reported_treated_female'];
        $data->lassa_fever_cases_reported_treated_total = $postData['lassa_fever_cases_reported_treated_total'];
        $data->drug_resistance_lassa_fever_cases_male = $postData['drug_resistance_lassa_fever_cases_male'];
        $data->drug_resistance_lassa_fever_cases_female = $postData['drug_resistance_lassa_fever_cases_female'];
        $data->drug_resistance_lassa_fever_cases_total = $postData['drug_resistance_lassa_fever_cases_total'];
        $data->number_of_new_cases_of_cholera_male = $postData['number_of_new_cases_of_cholera_male'];
        $data->number_of_new_cases_of_cholera_female = $postData['number_of_new_cases_of_cholera_female'];
        $data->number_of_new_cases_of_cholera_total = $postData['number_of_new_cases_of_cholera_total'];
        
        $data->save();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Record Add In Communicable Disease';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'communicable-disease';
        $data1['type'] = 'record_office';

        activity($data1);
         
        Session::flash('successMessage', 'Record added successfully!');
         return redirect()->route('communicable-disease');
    }
    
    public function update($id, Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $data = CommunicableDisease::find($id);
        $data->added_by = $userId;
        $data->month = $postData['month'] ;
        $data->hospital_id = Auth::user()->hospital_name;
        $data->year = $postData['year'] ;

        $data->new_malaria_cases_male = $postData['new_malaria_cases_male'];
        $data->new_malaria_cases_female = $postData['new_malaria_cases_female'];
        $data->new_malaria_cases_total = $postData['new_malaria_cases_total'];
        $data->clinic_tested_malaria_male = $postData['clinic_tested_malaria_male'];
        $data->clinic_tested_malaria_female = $postData['clinic_tested_malaria_female'];
        $data->clinic_tested_malaria_total = $postData['clinic_tested_malaria_total'];
        $data->malaria_cases_treated_with_act_male = $postData['malaria_cases_treated_with_act_male'];
        $data->malaria_cases_treated_with_act_female = $postData['malaria_cases_treated_with_act_female'];
        $data->malaria_cases_treated_with_act_total = $postData['malaria_cases_treated_with_act_total'];
        $data->malaria_in_pregnancy_male = $postData['malaria_in_pregnancy_male'];
        $data->malaria_in_pregnancy_female = $postData['malaria_in_pregnancy_female'];
        $data->malaria_in_pregnancy_total = $postData['malaria_in_pregnancy_total'];
        $data->drug_resistance_malaria_cases_male = $postData['drug_resistance_malaria_cases_male'];
        $data->drug_resistance_malaria_cases_female = $postData['drug_resistance_malaria_cases_female'];
        $data->drug_resistance_malaria_cases_total = $postData['drug_resistance_malaria_cases_total'];
        $data->malaria_severe_male = $postData['malaria_severe_male'];
        $data->malaria_severe_female = $postData['malaria_severe_female'];
        $data->malaria_severe_total = $postData['malaria_severe_total'];
        $data->number_of_new_hiv_cases_male = $postData['number_of_new_hiv_cases_male'];
        $data->number_of_new_hiv_cases_female = $postData['number_of_new_hiv_cases_female'];
        $data->number_of_new_hiv_cases_total = $postData['number_of_new_hiv_cases_total'];
        $data->number_of_co_factor_cases_tb_hiv_male = $postData['number_of_co_factor_cases_tb_hiv_male'];
        $data->number_of_co_factor_cases_tb_hiv_female = $postData['number_of_co_factor_cases_tb_hiv_female'];
        $data->number_of_co_factor_cases_tb_hiv_total = $postData['number_of_co_factor_cases_tb_hiv_total'];
        $data->number_of_hiv_persons_on_art_male = $postData['number_of_hiv_persons_on_art_male'];
        $data->number_of_hiv_persons_on_art_female = $postData['number_of_hiv_persons_on_art_female'];
        $data->number_of_hiv_persons_on_art_total = $postData['number_of_hiv_persons_on_art_total'];
        $data->number_of_dropouts_male = $postData['number_of_dropouts_male'];
        $data->number_of_dropouts_female = $postData['number_of_dropouts_female'];
        $data->number_of_dropouts_total = $postData['number_of_dropouts_total'];
        $data->death_complication_related_to_hiv_aids_male = $postData['death_complication_related_to_hiv_aids_male'];
        $data->death_complication_related_to_hiv_aids_female = $postData['death_complication_related_to_hiv_aids_female'];
        $data->death_complication_related_to_hiv_aids_total = $postData['death_complication_related_to_hiv_aids_total'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_male = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_male'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_female = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_female'];
        $data->new_case_of_severe_actute_respiratory_syndrome_sars_total = $postData['new_case_of_severe_actute_respiratory_syndrome_sars_total'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_male = $postData['new_case_of_severe_actute_respiratory_illness_sari_male'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_female = $postData['new_case_of_severe_actute_respiratory_illness_sari_female'];
        $data->new_case_of_severe_actute_respiratory_illness_sari_total = $postData['new_case_of_severe_actute_respiratory_illness_sari_total'];
        $data->new_case_of_neonatal_tetanus_male = $postData['new_case_of_neonatal_tetanus_male'];
        $data->new_case_of_neonatal_tetanus_female = $postData['new_case_of_neonatal_tetanus_female'];
        $data->new_case_of_neonatal_tetanus_total = $postData['new_case_of_neonatal_tetanus_total'];
        $data->new_case_of_measles_male = $postData['new_case_of_measles_male'];
        $data->new_case_of_measles_female = $postData['new_case_of_measles_female'];
        $data->new_case_of_measles_total = $postData['new_case_of_measles_total'];
        $data->new_case_of_onchcerciasis_male = $postData['new_case_of_onchcerciasis_male'];
        $data->new_case_of_onchcerciasis_female = $postData['new_case_of_onchcerciasis_female'];
        $data->new_case_of_onchcerciasis_total = $postData['new_case_of_onchcerciasis_total'];
        $data->new_case_of_poliomylitis_male = $postData['new_case_of_poliomylitis_male'];
        $data->new_case_of_poliomylitis_female = $postData['new_case_of_poliomylitis_female'];
        $data->new_case_of_poliomylitis_total = $postData['new_case_of_poliomylitis_total'];
        $data->new_case_of_rabies_human_male = $postData['new_case_of_rabies_human_male'];
        $data->new_case_of_rabies_human_female = $postData['new_case_of_rabies_human_female'];
        $data->new_case_of_rabies_human_total = $postData['new_case_of_rabies_human_total'];
        $data->new_case_of_smallpox_male = $postData['new_case_of_smallpox_male'];
        $data->new_case_of_smallpox_female = $postData['new_case_of_smallpox_female'];
        $data->new_case_of_smallpox_total = $postData['new_case_of_smallpox_total'];
        $data->new_case_of_sexually_transmitted_infection_stis_male = $postData['new_case_of_sexually_transmitted_infection_stis_male'];
        $data->new_case_of_sexually_transmitted_infection_stis_female = $postData['new_case_of_sexually_transmitted_infection_stis_female'];
        $data->new_case_of_sexually_transmitted_infection_stis_total = $postData['new_case_of_sexually_transmitted_infection_stis_total'];
        $data->new_case_of_yellow_fever_male = $postData['new_case_of_yellow_fever_male'];
        $data->new_case_of_yellow_fever_female = $postData['new_case_of_yellow_fever_female'];
        $data->new_case_of_yellow_fever_total = $postData['new_case_of_yellow_fever_total'];
        $data->new_case_of_finding_of_tuberculosis_male = $postData['new_case_of_finding_of_tuberculosis_male'];
        $data->new_case_of_finding_of_tuberculosis_female = $postData['new_case_of_finding_of_tuberculosis_female'];
        $data->new_case_of_finding_of_tuberculosis_total = $postData['new_case_of_finding_of_tuberculosis_total'];
        $data->tb_hiv_co_infection_case_male = $postData['tb_hiv_co_infection_case_male'];
        $data->tb_hiv_co_infection_case_female = $postData['tb_hiv_co_infection_case_female'];
        $data->tb_hiv_co_infection_case_total = $postData['tb_hiv_co_infection_case_total'];
        $data->tb_patient_on_art_male = $postData['tb_patient_on_art_male'];
        $data->tb_patient_on_art_female = $postData['tb_patient_on_art_female'];
        $data->tb_patient_on_art_total = $postData['tb_patient_on_art_total'];
        $data->multiple_drug_reaction_tb_cases_male = $postData['multiple_drug_reaction_tb_cases_male'];
        $data->multiple_drug_reaction_tb_cases_female = $postData['multiple_drug_reaction_tb_cases_female'];
        $data->multiple_drug_reaction_tb_cases_total = $postData['multiple_drug_reaction_tb_cases_total'];
        $data->number_of_new_cases_covid_19_male = $postData['number_of_new_cases_covid_19_male'];
        $data->number_of_new_cases_covid_19_female = $postData['number_of_new_cases_covid_19_female'];
        $data->number_of_new_cases_covid_19_total = $postData['number_of_new_cases_covid_19_total'];
        $data->clinic_tested_covid_19_male = $postData['clinic_tested_covid_19_male'];
        $data->clinic_tested_covid_19_female = $postData['clinic_tested_covid_19_female'];
        $data->clinic_tested_covid_19_total = $postData['clinic_tested_covid_19_total'];
        $data->cases_reported_treated_covid_19_male = $postData['cases_reported_treated_covid_19_male'];
        $data->cases_reported_treated_covid_19_female = $postData['cases_reported_treated_covid_19_female'];
        $data->cases_reported_treated_covid_19_total = $postData['cases_reported_treated_covid_19_total'];
        $data->drug_resistenace_covid_19_cases_male = $postData['drug_resistenace_covid_19_cases_male'];
        $data->drug_resistenace_covid_19_cases_female = $postData['drug_resistenace_covid_19_cases_female'];
        $data->drug_resistenace_covid_19_cases_total = $postData['drug_resistenace_covid_19_cases_total'];
        $data->number_of_new_faces_lassa_fever_male = $postData['number_of_new_faces_lassa_fever_male'];
        $data->number_of_new_faces_lassa_fever_female = $postData['number_of_new_faces_lassa_fever_female'];
        $data->number_of_new_faces_lassa_fever_total = $postData['number_of_new_faces_lassa_fever_total'];
        $data->clinic_tested_lassa_fever_male = $postData['clinic_tested_lassa_fever_male'];
        $data->clinic_tested_lassa_fever_female = $postData['clinic_tested_lassa_fever_female'];
        $data->clinic_tested_lassa_fever_total = $postData['clinic_tested_lassa_fever_total'];
        $data->lassa_fever_cases_reported_treated_male = $postData['lassa_fever_cases_reported_treated_male'];
        $data->lassa_fever_cases_reported_treated_female = $postData['lassa_fever_cases_reported_treated_female'];
        $data->lassa_fever_cases_reported_treated_total = $postData['lassa_fever_cases_reported_treated_total'];
        $data->drug_resistance_lassa_fever_cases_male = $postData['drug_resistance_lassa_fever_cases_male'];
        $data->drug_resistance_lassa_fever_cases_female = $postData['drug_resistance_lassa_fever_cases_female'];
        $data->drug_resistance_lassa_fever_cases_total = $postData['drug_resistance_lassa_fever_cases_total'];
        $data->number_of_new_cases_of_cholera_male = $postData['number_of_new_cases_of_cholera_male'];
        $data->number_of_new_cases_of_cholera_female = $postData['number_of_new_cases_of_cholera_female'];
        $data->number_of_new_cases_of_cholera_total = $postData['number_of_new_cases_of_cholera_total'];
        
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Edit In Communicable Disease';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'communicable-disease';
        $data1['type'] = 'record_office';

        activity($data1);
        Session::flash('successMessage', 'Record updated successfully!');
        
        return redirect()->route('communicable-disease');
    }
    
    public function filter_data(Request $request)
    {
        // pre($request->all());
        $hospitalname = $request->hospitalname;
        $added_by_filter = $request->added_by;
        $month_from = $request->month_from;
        $month_to = $request->month_to;

        $postData = $request->all();

        $userId = Auth::user()->id;   
        $data = CommunicableDisease::orderBy('id','desc')
            ->where(function ($q) use ($hospitalname,$added_by_filter,$month_from,$month_to) {
                if($hospitalname != ''){
                    $q->where('hospital_id', $hospitalname);
                }
                if($added_by_filter != ''){
                    $q->where('added_by', $added_by_filter);
                }
                if($month_from != ''){
                    $q->whereDate('created_at','>=', $month_from);
                }
                if($month_to != ''){
                    $q->whereDate('created_at','<=', $month_to);
                }
            })
            ->with('addedBy')->get();

        $hospital_name = Hospitals::get();
        $added_by = User::where('status','enable')->get();

        // pre($data);

        // return json_encode(array("status" => true,'data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by));

        return view('communicable-disease.view', ['data' => $data,'hospital_name' => $hospital_name,'added_by' => $added_by,'postData'=>$postData]);

    }
    
}
