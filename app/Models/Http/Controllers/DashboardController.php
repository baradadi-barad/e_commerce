<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hospitals;
use App\PatientSeen;
use App\User;
use App\MonthlyHospitalStatistics;
use App\MaternityReturns;
use App\GeneralOutpatient;
use App\AccidentEmergency;
use App\SpecialConsultiveClinics;
use App\DiseaseIndicators;
use App\InpatientRecords;
use App\CommunicableDisease;
use App\TotalFacilityAttendance;
use App\DiagnosisCount;
use DB;
use Auth;
use DateTime;
use Carbon\Carbon;

class DashboardController extends Controller
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
        // $timestamp = date("Y-m-d h:i:s", strtotime('-30 days'));
        // $today = date("Y-m-d h:i:s");
        $firstdateofmonth =  date('Y-m-01');
        $lastdateofmonth  =  date('Y-m-t');

        $data = MonthlyHospitalStatistics::select(DB::raw('SUM(total_total) as total_seen,SUM(anc_total) as anc_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
        $total = $data->total_seen;
        $anc_total = $data->anc_total;
        
        $patient = PatientSeen::select(DB::raw('SUM(clinical_unit) as total'),'hospitals.hospital_name')
                ->join('users',function($join){
                    $join->on('patient_seen.added_by','=','users.id');
                })
                ->join('hospitals',function($join){
                    $join->on('hospitals.id','=','users.hospital_name');
                })
                ->groupBy('users.hospital_name')
                ->orderBy("clinical_unit", "desc")
                ->take(5)
                ->get();


        $chart_data = array();
        foreach($patient as $key => $val)
        {
            $chart_data[$key]['device'] = $val['hospital_name'];
            $chart_data[$key]['seen'] = $val['total'];
        }

        $maternity_returns = MaternityReturns::select(DB::raw('SUM(live_birth_total) as live_birth_total,SUM(maternal_death_total) as maternal_death_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();        
        $live_birth_total = $maternity_returns->live_birth_total;
        $maternal_deaths_total = $maternity_returns->maternal_death_total;

        $general_outpatient = GeneralOutpatient::select(DB::raw('SUM(gopd_attendance_adult_total) as gopd_adult_total,SUM(gopd_attendance_pediatrics_total) as gopd_pediatrics_total,SUM(antenatal_attendance_total) as antenatal_total,SUM(antenatal_attendance_old_total) as antenatal_old_total,SUM(nhis_total) as nhis_total,SUM(fhis_total) as fhis_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();

        $gopd_total = $general_outpatient->gopd_adult_total + $general_outpatient->gopd_pediatrics_total;
        $anc_total = $general_outpatient->antenatal_total + $general_outpatient->antenatal_old_total;
        $insurance_total = $general_outpatient->nhis_total + $general_outpatient->fhis_total;

        $a_e_data = AccidentEmergency::select(DB::raw('SUM(a_e_attendance_total) as a_e_attendance_total'))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();
        $a_e_total = $a_e_data->a_e_attendance_total;

        $special_consultive_clinics = SpecialConsultiveClinics::select(DB::raw('
                        SUM(obstetrics_gynecology_clinic_total) as obstetrics_total,
                        SUM(surgical_clinic_total) as surgical_clinic_total,
                        SUM(medical_clinic_total) as medical_clinic_total,
                        SUM(ear_nose_throat_clinic_total) as ear_total,
                        SUM(dental_clinic_total) as dental_clinic_total,
                        SUM(ophthalmology_clinic_total) as ophthalmology_clinic_total,
                        SUM(optometric_clinic_total) as optometric_clinic_total,
                        SUM(urology_clinic_total) as urology_clinic_total,
                        SUM(orthopedics_clinic_total) as orthopedics_clinic_total,
                        SUM(pediatrics_clinic_total) as pediatrics_clinic_total,
                        SUM(physiotherapy_clinic_total) as physiotherapy_clinic_total,
                        SUM(comprehensive_site_total) as comprehensive_site_total,
                        SUM(neurology_clinic_total) as neurology_clinic_total,
                        SUM(nutrition_clinic_total) as nutrition_clinic_total,
                        SUM(dot_clinic_total) as dot_clinic_total,
                        SUM(peadiatrics_surgery_total) as peadiatrics_surgery_total,
                        SUM(dialysis_total) as dialysis_total,
                        SUM(total_dialysis_total) as total_dialysis_total,
                        SUM(dermatology_total) as dermatology_total,
                        SUM(pyschiatric_total) as pyschiatric_total,
                        SUM(plastic_total) as plastic_total


                '))
                ->where('created_at','>=', $firstdateofmonth)
                ->where('created_at','<=', $lastdateofmonth)
                ->first();  

        $s_c_attendance = $special_consultive_clinics->obstetrics_total+$special_consultive_clinics->surgical_clinic_total+$special_consultive_clinics->medical_clinic_total+$special_consultive_clinics->ear_total+$special_consultive_clinics->dental_clinic_total+$special_consultive_clinics->ophthalmology_clinic_total+$special_consultive_clinics->optometric_clinic_total+$special_consultive_clinics->urology_clinic_total+$special_consultive_clinics->orthopedics_clinic_total+$special_consultive_clinics->pediatrics_clinic_total+$special_consultive_clinics->physiotherapy_clinic_total+$special_consultive_clinics->comprehensive_site_total+$special_consultive_clinics->neurology_clinic_total+$special_consultive_clinics->nutrition_clinic_total+$special_consultive_clinics->dot_clinic_total+$special_consultive_clinics->peadiatrics_surgery_total+$special_consultive_clinics->dialysis_total+$special_consultive_clinics->total_dialysis_total+$special_consultive_clinics->dermatology_total+$special_consultive_clinics->pyschiatric_total+$special_consultive_clinics->plastic_total;     


        $inpatient_data = InpatientRecords::select(DB::raw('SUM(death_male) as death_male'),DB::raw('SUM(death_female) as death_female'))
            ->where('created_at','>=', $firstdateofmonth)
            ->where('created_at','<=', $lastdateofmonth)
            ->first();
        $hd_name_label = array();
        $final_death_data_array = array(); 
        $no_data_death = 0;
        $hd_name_label[0][] = "Male";
        $hd_name_label[0][] = isset($inpatient_data->death_male) ? $inpatient_data->death_male : 0;
        $hd_name_label[1][] = "Female";
        $hd_name_label[1][] = isset($inpatient_data->death_female) ? $inpatient_data->death_female : 0;
        $final_death_data_array = $hd_name_label;

        if($inpatient_data->death_male != '' || $inpatient_data->death_female != ''){
            $no_data_death = 1;
        }

        $maternity_data = MaternityReturns::select(DB::raw('SUM(live_birth_male) as live_birth_male'),DB::raw('SUM(live_birth_female) as live_birth_female'))
            ->where('created_at','>=', $firstdateofmonth)
            ->where('created_at','<=', $lastdateofmonth)
            ->first();
         
        $hm_name_label = array();
        $final_birth_data_array = array(); 
        $no_data_birth = 0;
        $hm_name_label[0][] = 'Male';
        $hm_name_label[0][] = isset($maternity_data->live_birth_male) ? $maternity_data->live_birth_male : 0;
        $hm_name_label[1][] = 'Female';
        $hm_name_label[1][] = isset($maternity_data->live_birth_female) ? $maternity_data->live_birth_female : 0;
        $final_birth_data_array = $hm_name_label;

        if($maternity_data->live_birth_male != '' || $maternity_data->live_birth_female != ''){
            $no_data_birth = 1; 
        }

        $start_date = date('Y-m-d', strtotime('Y-m-01'));
        for ($i = 0; $i < 6; $i++) 
        {
            $start_date_month[] = date("Y-m-01",strtotime("-$i months"));
            $end_date_month[] = date("Y-m-31",strtotime("-$i months"));
        }
        $final_array_maleria = array();
        $last_six_months = array();
        $addmited_patients = array();
        $total_patients = array();
        for($i = 0; $i < 6; $i++){
            $month_name = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('F');
            $year = DateTime::createFromFormat('Y-m-d', $start_date_month[$i])->format('Y');
            $communicable_diesease_data = CommunicableDisease::select(DB::raw('SUM(new_malaria_cases_total) as new_malaria_cases_total'))
                    ->where('created_at','>=', $start_date_month[$i])
                    ->where('created_at','<=', $end_date_month[$i])
                    ->first();
                     
            $last_six_months[] = substr(strtoupper($month_name),0,3);
            $final_array_maleria[] = isset($communicable_diesease_data->new_malaria_cases_total) ? $communicable_diesease_data->new_malaria_cases_total : 0;  

            $addmited_data = InpatientRecords::select(DB::raw('SUM(admission_total) as admission_total'))
                ->where('created_at','>=', $start_date_month[$i])
                ->where('created_at','<=', $end_date_month[$i])
                ->first();
            $addmited_patients[] = isset($addmited_data->admission_total) ? $addmited_data->admission_total : 0;  

            $patients_data = TotalFacilityAttendance::select(DB::raw('SUM(total_facility_attendance_total) as total_facility_attendance_total'))
                ->where('created_at','>=', $start_date_month[$i])
                ->where('created_at','<=', $end_date_month[$i])
                ->first();
            $total_patients[] = isset($patients_data->total_facility_attendance_total) ? $patients_data->total_facility_attendance_total : 0;  

            $diabetes_count = DiagnosisCount::select(DB::raw('SUM(diabetes_total) as diabetes_total'),DB::raw('SUM(hypertension_total) as hypertension_total'))
                ->where('created_at','>=', $start_date_month[$i])
                ->where('created_at','<=', $end_date_month[$i])
                ->first();
            $diabetes[] = isset($diabetes_count->diabetes_total) ? $diabetes_count->diabetes_total : 0;  
            $hypertension[] = isset($diabetes_count->hypertension_total) ? $diabetes_count->hypertension_total : 0;  
        }
        
        $data = User::where('status','enable')->get();

        $record = Hospitals::where('status','enable')->count();

        return view('dashboard.dashboard',compact('record','total','chart_data','data','live_birth_total','maternal_deaths_total','anc_total','gopd_total','insurance_total','a_e_total','s_c_attendance','final_death_data_array','final_array_maleria','last_six_months','final_birth_data_array','addmited_patients','total_patients','no_data_birth','no_data_death','diabetes','hypertension'));
    }

    public function get_diff(Request $request){
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

    public function get_dis_ani(Request $request){
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

    public function accessDenied()
    {
        return view('access-denied');
    }
}
