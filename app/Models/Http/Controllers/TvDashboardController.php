<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\MaternityReturns;
use App\GeneralOutpatient;
use App\AccidentEmergency;
use App\HealthInsurance;
use App\InpatientRecords;
use App\CommunicableDisease;
use App\DiseaseIndicators;
use DB;
use DateTime;
use Carbon\Carbon;

class TvDashboardController extends Controller
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
        $firstdateofmonth =  date('Y-m-01');
        $lastdateofmonth  =  date('Y-m-t');
        
        $maternity_returns = MaternityReturns::select(DB::raw('SUM(live_birth_total) as live_birth_total,SUM(live_birth_male) as live_birth_male,SUM(live_birth_female) as live_birth_female'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();        
        $live_birth_total = $maternity_returns->live_birth_total;

        $general_outpatient = GeneralOutpatient::select(DB::raw('SUM(gopd_attendance_adult_total) as gopd_adult_total,SUM(gopd_attendance_pediatrics_total) as gopd_pediatrics_total,SUM(antenatal_attendance_total) as antenatal_total,SUM(antenatal_attendance_old_total) as antenatal_old_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();

        $gopd_total = $general_outpatient->gopd_adult_total + $general_outpatient->gopd_pediatrics_total;
        $anc_total = $general_outpatient->antenatal_total + $general_outpatient->antenatal_old_total;

        $general_out = HealthInsurance::select(DB::raw('SUM(nhis_enrolled_total) as nhis_enrolled_total,SUM(fhis_enrolled_total) as fhis_enrolled_total'))
                ->first();
        $insurance_total = $general_out->nhis_enrolled_total + $general_out->fhis_enrolled_total;


        $a_e_data = AccidentEmergency::select(DB::raw('SUM(a_e_attendance_total) as a_e_attendance_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
        $a_e_total = $a_e_data->a_e_attendance_total;

        $inpatient_record = InpatientRecords::select(DB::raw('SUM(admission_male) as admission_male,SUM(admission_female) as admission_female,SUM(admission_total) as admission_total,SUM(discharges_total) as discharges_total,SUM(death_total) as death_total,added_by,hospital_id'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
        $admission_male = $inpatient_record->admission_male;
        $admission_female = $inpatient_record->admission_female;
        $admission_total = $inpatient_record->admission_total;
        $death_total = $inpatient_record->death_total;    

        $start_date_month = array();
        $end_date_month = array();
        for ($i = 0; $i < 6; $i++) 
        {
            $start_date_month[] = date("Y-m-01",strtotime("-$i months"));
            $end_date_month[] = date("Y-m-31",strtotime("-$i months"));
        }
        $last_six_months = array();
        for($i = 0; $i < 6; $i++){
                $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
                $accident_data = AccidentEmergency::select(DB::raw('SUM(a_e_attendance_male) as a_e_attendance_male,SUM(a_e_attendance_female) as a_e_attendance_female'))
                                ->where('created_at','>=', $start_date_month[$i])
                                ->where('created_at','<=', $end_date_month[$i])
                                ->first();

                $data_accident_male[] = isset($accident_data->a_e_attendance_male) ? $accident_data->a_e_attendance_male : 0;   
                $data_accident_female[] = isset($accident_data->a_e_attendance_female) ? $accident_data->a_e_attendance_female : 0;

                $communicable = CommunicableDisease::select(DB::raw('SUM(new_malaria_cases_total) as new_malaria_cases_total'))
                                ->where('created_at','>=', $start_date_month[$i])
                                ->where('created_at','<=', $end_date_month[$i])
                                ->first();

                $malaria_data[] = isset($communicable->new_malaria_cases_total) ? $communicable->new_malaria_cases_total : 0;

                $general_data = GeneralOutpatient::select(DB::raw('SUM(antenatal_attendance_total) as antenatal_total,SUM(antenatal_attendance_old_total) as antenatal_old_total'))
                        ->where('created_at','>=', $start_date_month[$i])
                        ->where('created_at','<=', $end_date_month[$i])
                        ->first();

                $total = 0;
                $total = $general_data->antenatal_total + $general_data->antenatal_old_total;

                $antenatal_total[] = isset($total) ? $total : 0; 
                $last_six_months[] = substr(strtoupper($month_name),0,3);
        }

        $start_date_month = array();
        $end_date_month = array();
        for ($i = 0; $i < 12; $i++) 
        {
            $start_date_month[] = date("Y-m-01",strtotime("-$i months"));
            $end_date_month[] = date("Y-m-31",strtotime("-$i months"));
        }
        $last_twelve_months = array();
        for($i = 0; $i < 12; $i++){
                $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
                  
        }

        $start_date_month = array();
        $end_date_month = array();
        for ($i = 0; $i < 4; $i++) 
        {
            $start_date_month[] = date("Y-m-01",strtotime("-$i months"));
            $end_date_month[] = date("Y-m-31",strtotime("-$i months"));
        }
        $last_four_months = array();
        for($i = 0; $i < 4; $i++){
            $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
            $communicable = CommunicableDisease::select(DB::raw('SUM(malaria_cases_treated_with_act_total) as malaria_cases_act_total,SUM(new_malaria_cases_total) as new_malaria_cases_total,SUM(number_of_new_faces_lassa_fever_total) as lassa_fever_total,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as sars_total,SUM(new_case_of_smallpox_total) as smallpox_total,SUM(number_of_new_cases_of_cholera_total) as cholera_total,SUM(new_case_of_measles_total) as measles_total,SUM(number_of_new_cases_covid_19_total) as covid_total,SUM(new_case_of_yellow_fever_total) as yellow_fever_total'))
                ->where('created_at','>=', $start_date_month[$i])
                ->where('created_at','<=', $end_date_month[$i])
                ->first();
                $malaria_cases_act_total[] = isset($communicable->malaria_cases_act_total) ? $communicable->malaria_cases_act_total : 0;
                $new_malaria_cases_total[] = isset($communicable->new_malaria_cases_total) ? $communicable->new_malaria_cases_total : 0;
                $lassa_fever_total[] = isset($communicable->lassa_fever_total) ? $communicable->lassa_fever_total : 0;
                $sars_total[] = isset($communicable->sars_total) ? $communicable->sars_total : 0;
                $smallpox_total[] = isset($communicable->smallpox_total) ? $communicable->smallpox_total : 0;
                $cholera_total[] = isset($communicable->cholera_total) ? $communicable->cholera_total : 0;
                $measles_total[] = isset($communicable->measles_total) ? $communicable->measles_total : 0;
                $covid_total[] = isset($communicable->covid_total) ? $communicable->covid_total : 0;
                $yellow_fever_total[] = isset($communicable->yellow_fever_total) ? $communicable->yellow_fever_total : 0;

            $last_four_months[] = substr(strtoupper($month_name),0,5);
        }

        $malaria_cases_act_total = array_reverse($malaria_cases_act_total);
        $new_malaria_cases_total = array_reverse($new_malaria_cases_total);
        $lassa_fever_total = array_reverse($lassa_fever_total);
        $sars_total = array_reverse($sars_total);
        $smallpox_total = array_reverse($smallpox_total);
        $cholera_total = array_reverse($cholera_total);
        $measles_total = array_reverse($measles_total);
        $covid_total = array_reverse($covid_total);
        $yellow_fever_total = array_reverse($yellow_fever_total);

        $inpatient_data_array = InpatientRecords::select(DB::raw('SUM(death_male) as death_male'),DB::raw('SUM(death_female) as death_female'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
                $hd_name_label = array();
                $final_death_data_array = array(); 
                $no_data_death = 0;
                $hd_name_label[0][] = "Male";
                $hd_name_label[0][] = isset($inpatient_data_array->death_male) ? $inpatient_data_array->death_male : 0;
                $hd_name_label[1][] = "Female";
                $hd_name_label[1][] = isset($inpatient_data_array->death_female) ? $inpatient_data_array->death_female : 0;
                $final_death_data_array = $hd_name_label;

                if($inpatient_data_array->death_male != '' || $inpatient_data_array->death_female != ''){
                $no_data_death = 1;
                }

        $maternity_data_array = MaternityReturns::select(DB::raw('SUM(live_birth_male) as live_birth_male'),DB::raw('SUM(live_birth_female) as live_birth_female'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
         
                $hm_name_label = array();
                $final_birth_data_array = array(); 
                $no_data_birth = 0;
                $hm_name_label[0][] = 'Male';
                $hm_name_label[0][] = isset($maternity_data_array->live_birth_male) ? $maternity_data_array->live_birth_male : 0;
                $hm_name_label[1][] = 'Female';
                $hm_name_label[1][] = isset($maternity_data_array->live_birth_female) ? $maternity_data_array->live_birth_female : 0;
                $final_birth_data_array = $hm_name_label;

                if($maternity_data_array->live_birth_male != '' || $maternity_data_array->live_birth_female != ''){
                $no_data_birth = 1; 
                }

        $last_four_months = array_reverse($last_four_months);
        $last_six_months = array_reverse($last_six_months);
        $malaria_data = array_reverse($malaria_data);
        $data_accident_male = array_reverse($data_accident_male);
        $data_accident_female = array_reverse($data_accident_female);
        $antenatal_total = array_reverse($antenatal_total);
                
                $no_data_accident = 0;
                if(!empty($data_accident_male) || !empty($data_accident_female)){
                        $no_data_accident = 1;
                }
                $no_data_maleria = 0;
                if(!empty($malaria_data)){
                        $no_data_maleria = 1;
                }
                $no_data_anc = 0;
                if(!empty($antenatal_total)){
                        $no_data_anc = 1;
                }
                $no_data_accident = 0;
                if(!empty($admission_male) || !empty($admission_female)){
                        $no_data_admission = 1;
                }

        $communicable_record = CommunicableDisease::select(DB::raw('SUM(malaria_cases_treated_with_act_total) as malaria_cases_act_total,SUM(new_malaria_cases_total) as new_malaria_cases_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
        $new_malaria_cases = $communicable_record->new_malaria_cases_total;
        $malaria_cases_act = $communicable_record->malaria_cases_act_total;

        return view('dashboard.tv-dashboard',compact('live_birth_total','anc_total','gopd_total','insurance_total','a_e_total','admission_total','death_total','malaria_cases_act','new_malaria_cases','last_six_months','last_twelve_months','last_four_months','final_death_data_array','final_birth_data_array','no_data_birth','no_data_death','malaria_data','data_accident_male','data_accident_female','no_data_accident','no_data_maleria','admission_male','admission_female','antenatal_total','no_data_anc','no_data_admission','malaria_cases_act_total','new_malaria_cases_total','lassa_fever_total','sars_total','smallpox_total','cholera_total','measles_total','covid_total','yellow_fever_total'));
    }

    public function get_differance(Request $request){
        $animation = 0;
        $firstdateofmonth =  date('Y-m-01');
        $lastdateofmonth  =  date('Y-m-t');
        $animation_id = '';
        $communicable = CommunicableDisease::select(DB::raw('SUM(number_of_new_faces_lassa_fever_total) as lassa_fever,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as sars,SUM(new_case_of_smallpox_total) as small_pox,SUM(number_of_new_cases_of_cholera_total) as cholera,SUM(new_case_of_measles_total) as measles,SUM(number_of_new_cases_covid_19_total) as covid,SUM(new_case_of_yellow_fever_total) as yellow_fever'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
                $total_count = 0;
        $data = DiseaseIndicators::orderBy('id','desc')->first();

        if(!empty($data) && $data != ''){
                $date1 = $data->create_date;
                $date2 = date('Y-m-d H:i:s');
                $hours = round((strtotime($date2) - strtotime($date1))/3600, 2);
                if($hours < 72){
                        if(!empty($communicable)){
                                $total_count = $communicable->lassa_fever+$communicable->sars+$communicable->small_pox+$communicable->cholera+$communicable->measles+$communicable->covid+$communicable->yellow_fever;   
                                // echo $data->total_count; exit; 
                        }
                        if($total_count == $data->total_count){
                                $animation_id = $data->id;
                                $animation = 1;
                        }else{
                                $animation_id = $data->id;
                                $animation = 2;
                        }
                }else{
                        if(!empty($communicable)){
                                $total_count = $communicable->lassa_fever+$communicable->sars+$communicable->small_pox+$communicable->cholera+$communicable->measles+$communicable->covid+$communicable->yellow_fever;
        
                                $disease_indicators = DiseaseIndicators::find($data->id);
                                $disease_indicators->added_by = Auth::user()->id;
                                $disease_indicators->lassa_fever = $communicable->lassa_fever;
                                $disease_indicators->sars = $communicable->sars;
                                $disease_indicators->small_pox = $communicable->small_pox;
                                $disease_indicators->cholera = $communicable->cholera;
                                $disease_indicators->measles = $communicable->measles;
                                $disease_indicators->yellow_fever = $communicable->yellow_fever;
                                $disease_indicators->covid = $communicable->covid;
                                $disease_indicators->total_count = $total_count;
                                $disease_indicators->create_date = date('Y-m-d H:i:s');
                                $disease_indicators->month_start = $firstdateofmonth;
                                $disease_indicators->month_end = $lastdateofmonth;
                                $disease_indicators->save();
        
                                $animation_id = $disease_indicators->id;
                        }
                }
        }else{
                if(!empty($communicable)){
                        $total_count = $communicable->lassa_fever+$communicable->sars+$communicable->small_pox+$communicable->cholera+$communicable->measles+$communicable->covid+$communicable->yellow_fever;

                        $disease_indicators = new DiseaseIndicators();
                        $disease_indicators->added_by = Auth::user()->id;
                        $disease_indicators->lassa_fever = $communicable->lassa_fever;
                        $disease_indicators->sars = $communicable->sars;
                        $disease_indicators->small_pox = $communicable->small_pox;
                        $disease_indicators->cholera = $communicable->cholera;
                        $disease_indicators->measles = $communicable->measles;
                        $disease_indicators->covid = $communicable->covid;
                        $disease_indicators->yellow_fever = $communicable->yellow_fever;
                        $disease_indicators->total_count = $total_count;
                        $disease_indicators->month_start = $firstdateofmonth;
                        $disease_indicators->create_date = date('Y-m-d H:i:s');
                        $disease_indicators->month_end = $lastdateofmonth;
                        $disease_indicators->save();

                        $animation_id = $disease_indicators->id;
                        $animation = 2;
                }
        }
        
        return json_encode(array("status" => true,"animation" => $animation,'animation_id' => $animation_id));
    }

    public function get_disble_ani(Request $request){
        $firstdateofmonth =  date('Y-m-01');
        $lastdateofmonth  =  date('Y-m-t');
        $disease_indicator_id = $request->id;
        $communicable = CommunicableDisease::select(DB::raw('SUM(number_of_new_faces_lassa_fever_total) as lassa_fever,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as sars,SUM(new_case_of_smallpox_total) as small_pox,SUM(number_of_new_cases_of_cholera_total) as cholera,SUM(new_case_of_measles_total) as measles,SUM(number_of_new_cases_covid_19_total) as covid,SUM(new_case_of_yellow_fever_total) as yellow_fever'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();

                if(!empty($communicable)){
                        $total_count = $communicable->lassa_fever+$communicable->sars+$communicable->small_pox+$communicable->cholera+$communicable->measles+$communicable->covid+$communicable->yellow_fever;

                        $disease_indicators = DiseaseIndicators::find($disease_indicator_id);
                        $disease_indicators->added_by = Auth::user()->id;
                        $disease_indicators->lassa_fever = $communicable->lassa_fever;
                        $disease_indicators->sars = $communicable->sars;
                        $disease_indicators->small_pox = $communicable->small_pox;
                        $disease_indicators->cholera = $communicable->cholera;
                        $disease_indicators->measles = $communicable->measles;
                        $disease_indicators->yellow_fever = $communicable->yellow_fever;
                        $disease_indicators->covid = $communicable->covid;
                        $disease_indicators->total_count = $total_count;
                        $disease_indicators->create_date = date('Y-m-d H:i:s');
                        $disease_indicators->month_start = $firstdateofmonth;
                        $disease_indicators->month_end = $lastdateofmonth;
                        $disease_indicators->save();

                        $animation_id = $disease_indicator_id;
                }
        return json_encode(array("status" => true,'animation_id' => $animation_id));
    }
}
