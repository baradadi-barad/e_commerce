<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PatientGeneralStatistics;
use Session;
use Auth;
use Carbon\Carbon;
use App\PatientSeenReport;
use App\AccidentEmergency;
use App\GeneralOutpatient;
use App\LaboratoryInvestigations;
use App\Operations;
use App\User;
use App\DoctorPerformance;
use App\Hospitals;
use View;
use DB;
use App\SurgeonPerformanceReport;
use App\PatientSeen;
use App\SrhHivIntegration;
use App\InpatientRecords;
use App\TBHiv;
use App\PMTCTMother;
use App\TbLpForm;
use App\PmctcInfantForm;
use App\HivTbMalariaIntegratedServices;
use App\SpecialConsultiveClinics;
use App\RadioDiagnosis;
use App\MaternityReturns;
use App\MalariaPreventation;
use App\Nutrition;
use App\Imci;
use App\FamilyPlanning;
use App\Referrals;
use App\Immunization;
use App\NonCommunicableDiseases;
use App\FamilyPlanningRecordOffice;
use App\CommunicableDisease;
use App\TotalFacilityAttendance;
use App\MonthlyHospitalStatistics;
use App\ImmunizationClinic;
use App\HealthInsurance;

class ReportsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        deleteNotification();
    }
    
    public function advSearch(Request $request){
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        $current_month = date('m');
        
       return view('reports.adv-search',['years'=>$years],['currentMonth'=>$current_month]);
    }
    
    public function patientGeneralStatisticsReport(Request $request) {
        $current_year = date('Y');
        $years = range($current_year - 5, $current_year + 10);
        $hospitalname1= '';
        $hospital_name3=[];
        $request->flash();
        $postData = $request->all();
        $hospital_name = Hospitals::get();
        $data = array();
         if(isset($postData) && count($postData) > 0 ){
            if($postData['hospitalname'] != ''){
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                if($hospital_name2 != '' && count($hospital_name2) > 0){
                    foreach ($hospital_name2 as $value) {
                        $hospital_name3[]= $value->id;
                    }
                }
            }
        }
        if (isset($postData) && count($postData) > 0) {

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($hospitalname1 == '' ){
                $data = PatientGeneralStatistics::select(DB::raw('SUM(no_of_patient_seen_male) as no_of_patient_seen_male,
                                                    SUM(no_of_patient_seen_female) as no_of_patient_seen_female,
                                                    SUM(no_of_delivery_male) as no_of_delivery_male,
                                                    SUM(no_of_delivery_female) as no_of_delivery_female,
                                                    SUM(no_of_deaths_male) as no_of_deaths_male,
                                                    SUM(no_of_deaths_female) as no_of_deaths_female,
                                                    SUM(no_of_admission_male) as no_of_admission_male,
                                                    SUM(no_of_admission_female) as no_of_admission_female,
                                                    SUM(no_of_patient_sc_male) as no_of_patient_sc_male,
                                                    SUM(no_of_patient_sc_female) as no_of_patient_sc_female,
                                                    SUM(no_of_discharges_male) as no_of_discharges_male,
                                                    SUM(no_of_discharges_female) as no_of_discharges_female,
                                                    SUM(registered_anc_attendees) as registered_anc_attendees,
                                                    SUM(internally_generated_revenue) as internally_generated_revenue,
                                                    SUM(registered_anc_attendees_under5m) as registered_anc_attendees_under5m,
                                                    SUM(registered_anc_attendees_under5f) as registered_anc_attendees_under5f,added_by
                                              '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                    ->first();
            }else{
                $data = PatientGeneralStatistics::select(DB::raw('SUM(no_of_patient_seen_male) as no_of_patient_seen_male,
                                                    SUM(no_of_patient_seen_female) as no_of_patient_seen_female,
                                                    SUM(no_of_delivery_male) as no_of_delivery_male,
                                                    SUM(no_of_delivery_female) as no_of_delivery_female,
                                                    SUM(no_of_deaths_male) as no_of_deaths_male,
                                                    SUM(no_of_deaths_female) as no_of_deaths_female,
                                                    SUM(no_of_admission_male) as no_of_admission_male,
                                                    SUM(no_of_admission_female) as no_of_admission_female,
                                                    SUM(no_of_patient_sc_male) as no_of_patient_sc_male,
                                                    SUM(no_of_patient_sc_female) as no_of_patient_sc_female,
                                                    SUM(no_of_discharges_male) as no_of_discharges_male,
                                                    SUM(no_of_discharges_female) as no_of_discharges_female,
                                                    SUM(registered_anc_attendees) as registered_anc_attendees,
                                                    SUM(internally_generated_revenue) as internally_generated_revenue,
                                                    SUM(registered_anc_attendees_under5m) as registered_anc_attendees_under5m,
                                                    SUM(registered_anc_attendees_under5f) as registered_anc_attendees_under5f,added_by
                                              '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                            if($hospitalname1 != ''){
                                $q->whereIn('added_by', $hospital_name3);
                            }
                        })->first();
            }
        }

        // echo "<pre>"; print_r($data); exit;
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.patient-general-statistics-report', [
            'postData' => $postData,
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years]);
    }

    public function patientSeenReport(Request $request) {

        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        
        $request->flash();
        $postData = $request->all();
        $patientreportData = array();
         
        $total = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        if (isset($postData) && count($postData) > 0) {
            if($hospitalname1 == '' ){
                
                $patientreportData = PatientSeenReport::groupBy('doctors_name')->select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e, SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental, doctors_name'),"added_by")
                        ->with('addedBy')
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->get();



                $total = PatientSeenReport::select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e,'
                                        . ' SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental'))
                                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        
                        ->first();
                $total->doctors_name = 'Sum Total';
                $total->id = '';

            }else{
                $patientreportData = PatientSeenReport::groupBy('doctors_name')->select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e, SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental, doctors_name'),"added_by")
                        ->with('addedBy')
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                               //->where('year', $postData['year'])

                         ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                        ->get();



                $total = PatientSeenReport::select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e,'
                                        . ' SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental'))
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                               //->where('year', $postData['year'])

                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                        ->first();
            }

        }

        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.patient-seen-report', [
            'patientreportData' => $patientreportData,
            'years' => $years,
            'postData' => $postData,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'totalRow' => $total]);
    }
    
    public function doctorPerformanceReport( Request $request){
        
        $current_year = date('Y');
        $years = range($current_year-5, $current_year+10);
        
        $request->flash();
        $postData = $request->all();
        $hospital_name = Hospitals::get();
        $data = array();
        $temp = array();
        if (isset($postData) && count($postData) > 0) {

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            $hospitals = Hospitals::get()->toArray();
            foreach ($hospitals as $hospital) {
                $temp[$hospital['id']] = DoctorPerformance :: where('hospital_id', $hospital['id'])
                                ->where('month',$postData['month'])
                                ->where('year',$postData['year'])
                                ->select(DB::raw('SUM(no_of_patient_seen) as patienSeen, SUM(no_of_operation) as operationDone ,doctor_id'))
                                ->groupBy('doctor_id')->get()->toArray();
            }
            $temp = array_filter($temp);
            $minPatientSeenDoctor = array();
            $minOperationDoctor = array();
            foreach ($hospitals as $hospital) {
                $id = $hospital['id'];
                if (isset($temp[$id])) {
                    $maxPatientSeen = 0;
                    $maxPatientSeenDoctor = '';
                    $minPatientSeen = 0;
                    $minPatientSeenDoctor = '';
                    $maxOperation = 0;
                    $maxOperationDoctor = '';
                    $minOperation = 0;
                    $minOperationDoctor = '';
                    foreach ($temp[$id] as $dp) {
                        if ($maxPatientSeen < $dp['patienSeen']) {
                            $maxPatientSeen = $dp['patienSeen'];
                            $maxPatientSeenDoctor = $dp['doctor_id'];
                        }
                        if ($minPatientSeen == 0) {
                            $minPatientSeen = $dp['patienSeen'];
                        }
                        if( $minPatientSeen == $dp['patienSeen']){
                            $minPatientSeenDoctor[] = $dp['doctor_id'];
                        }
                        if ($minPatientSeen > $dp['patienSeen']) {
                            $minPatientSeenDoctor = array();
                            $minPatientSeen = $dp['patienSeen'];
                            $minPatientSeenDoctor[] = $dp['doctor_id'];
                        }
                        if ($maxOperation < $dp['operationDone']) {
                            $maxOperation = $dp['operationDone'];
                            $maxOperationDoctor = $dp['doctor_id'];
                        }
                        if ($minOperation == 0) {
                            $minOperation = $dp['operationDone'];
                        }
                        if($minOperation == $dp['operationDone']){
                            $minOperationDoctor[] = $dp['doctor_id'];
                        }
                        if ($minOperation > $dp['operationDone']) {
                            $minOperationDoctor = array();
                            $minOperation = $dp['operationDone'];
                            $minOperationDoctor[] = $dp['doctor_id'];
                        }
                    }

                    if ($maxPatientSeenDoctor != '') {
                        $temp[$id]['maxPatientSeen'] = $maxPatientSeen . ' / ' . $maxPatientSeenDoctor;
                    } else {
                        $temp[$id]['maxPatientSeen'] = '-';
                    }

                    if ($minPatientSeenDoctor != '') {
                        $temp[$id]['minPatientSeen'] = $minPatientSeen . ' / ' . implode(',',$minPatientSeenDoctor);
                    } else {
                        $temp[$id]['minPatientSeen'] = '-';
                    }

                    if ($maxOperationDoctor != '') {
                        $temp[$id]['maxOperation'] = $maxOperation . ' / ' . $maxOperationDoctor;
                    } else {
                        $temp[$id]['maxOperation'] = '-';
                    }
                    if ($minOperationDoctor != '') {
                        $temp[$id]['minOperation'] = $minOperation . ' / ' . implode(',',$minOperationDoctor);
                    } else {
                        $temp[$id]['minOperation'] = '-';
                    }
                    $temp[$id]['hospital_name'] = $hospital['hospital_name'];
                }
            }
        }

        return view('reports.doctor-performance-report', [
            'postData' =>$postData,
            'hospital_name' => $hospital_name,
            'data' =>$temp,
            'years'=>$years ]);
    }

    public function surgeonPerformanceReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];

        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                                
                $hospitalname1 = $postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if (isset($postData) && count($postData) > 0) {
             if($hospitalname1 == '' ){
                    $data = SurgeonPerformanceReport::groupBy('doctors_name')->select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female ,doctors_name'),"added_by")
                            ->with('addedBy')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->get();
 
                    $total = SurgeonPerformanceReport::select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->first();

                    $total->doctors_name = 'Sum Total';
                 $data[count($data)] = $total;
             }else{
                 $data = SurgeonPerformanceReport::groupBy('doctors_name')->select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female ,doctors_name'),"added_by")
                        ->with('addedBy')
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                               //->where('year', $postData['year'])

                         ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                        ->get();

                $total = SurgeonPerformanceReport::select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female'))
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                               //->where('year', $postData['year'])

                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                        ->first();

                $total->doctors_name = 'Sum Total';
                $data[count($data)] = $total;
            }
        }
 
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        } 

        return view('reports.surgeon-performance-report', [
            'data' => $data,
            'years' => $years,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }

    public function patientSeen(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        
        $request->flash();
        $postData = $request->all();
        $data = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        

        if (isset($postData) && count($postData) > 0) {
             if($hospitalname1 == '' ){
                    $data = PatientSeen::groupBy('doctors_name')->select(DB::raw('SUM(clinical_unit) as clinical_unit,doctors_name'),'added_by')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->with('addedBy')
                            ->get();
                    $total = PatientSeen::select(DB::raw('SUM(clinical_unit) as clinical_unit'))
                           ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->first();

                        

                    $total->doctors_name = 'Sum Total';
                    $data[count($data)] = $total;
             }else{
                 $data = PatientSeen::groupBy('doctors_name')->select(DB::raw('SUM(clinical_unit) as clinical_unit,doctors_name'),'added_by')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                             ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                            ->with('addedBy')
                            ->get();
                    $total = PatientSeen::select(DB::raw('SUM(clinical_unit) as clinical_unit'))
                           ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                             ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                            ->first();
                    

                    $total->doctors_name = 'Sum Total';
                    $data[count($data)] = $total;
             }
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
        
        return view('reports.patient-seen', [
            'data' => $data,
            'years' => $years,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }
    
    public function srhHivIntegration(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = SrhHivIntegration::select(DB::raw('SUM(hct_clients_provided_male) as hct_clients_provided_male,
                                                    SUM(hct_clients_provided_female) as hct_clients_provided_female,
                                                    SUM(hct_clients_provided_total) as hct_clients_provided_total,
                                                    SUM(hct_clients_referred_male) as hct_clients_referred_male,
                                                    SUM(hct_clients_referred_female) as hct_clients_referred_female,
                                                    SUM(hct_clients_referred_total) as hct_clients_referred_total,
                                                    SUM(hct_clients_screened_male) as hct_clients_screened_male,
                                                    SUM(hct_clients_screened_female) as hct_clients_screened_female,
                                                    SUM(hct_clients_screened_total) as hct_clients_screened_total,
                                                    SUM(hct_clients_treated_male) as hct_clients_treated_male,
                                                    SUM(hct_clients_treated_female) as hct_clients_treated_female,
                                                    SUM(hct_clients_treated_total) as hct_clients_treated_total,
                                                    SUM(fp_clients_provided_male) as fp_clients_provided_male,
                                                    SUM(fp_clients_provided_female) as fp_clients_provided_female,
                                                    SUM(fp_clients_provided_tootal) as fp_clients_provided_tootal
                                              '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                    ->first();
               }else{
                   $data = SrhHivIntegration::select(DB::raw('SUM(hct_clients_provided_male) as hct_clients_provided_male,
                                                    SUM(hct_clients_provided_female) as hct_clients_provided_female,
                                                    SUM(hct_clients_provided_total) as hct_clients_provided_total,
                                                    SUM(hct_clients_referred_male) as hct_clients_referred_male,
                                                    SUM(hct_clients_referred_female) as hct_clients_referred_female,
                                                    SUM(hct_clients_referred_total) as hct_clients_referred_total,
                                                    SUM(hct_clients_screened_male) as hct_clients_screened_male,
                                                    SUM(hct_clients_screened_female) as hct_clients_screened_female,
                                                    SUM(hct_clients_screened_total) as hct_clients_screened_total,
                                                    SUM(hct_clients_treated_male) as hct_clients_treated_male,
                                                    SUM(hct_clients_treated_female) as hct_clients_treated_female,
                                                    SUM(hct_clients_treated_total) as hct_clients_treated_total,
                                                    SUM(fp_clients_provided_male) as fp_clients_provided_male,
                                                    SUM(fp_clients_provided_female) as fp_clients_provided_female,
                                                    SUM(fp_clients_provided_tootal) as fp_clients_provided_tootal
                                              '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                    ->first();
               }
            
        }

         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.srh-hiv-integration', [
            'data' => $data,
            'years' => $years,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }

    public function InpatientRecords(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $inpatient_records = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
            
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['inpatient_records'] != ''){
                $inpatient_records = $postData['inpatient_records'];
            }
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) {
                $postData['month_from'] = str_replace('/','-',$postData['month_from']);
                $postData['month_to'] = str_replace('/','-',$postData['month_to']);
                if($hospitalname1 == '' && $inpatient_records){
                   $data = InpatientRecords:: select(DB::raw('SUM(admission_male) as admission_male,
                                            SUM(admission_female) as admission_female,
                                            SUM(admission_total) as admission_total,
                                            SUM(discharges_male) as discharges_male,
                                            SUM(discharges_female) as discharges_female,
                                            SUM(discharges_total) as discharges_total,
                                            SUM(death_male) as death_male,
                                            SUM(death_female) as death_female,
                                            SUM(death_total) as death_total,added_by,hospital_id'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->groupBy('added_by')
                            ->get();

                    foreach($data as $key => $value1){ 
                        $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;   
                    }
                   
                }else{
                    $data = InpatientRecords::select(DB::raw('SUM(admission_male) as admission_male,SUM(admission_female) as admission_female,SUM(admission_total) as admission_total,SUM(discharges_male) as discharges_male,SUM(discharges_female) as discharges_female,SUM(discharges_total) as discharges_total,SUM(death_male) as death_male,SUM(death_female) as death_female,SUM(death_total) as death_total'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                if($hospitalname1 != ''){
                                    $q->whereIn('added_by', $hospital_name3);
                                }
                            })
                        ->first();
                }
           }
             $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name = Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $inpatient_records != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($inpatient_records == 'admission' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->admission_male, "Female" => $value->admission_female, "Total" => $value->admission_total);
                        }
                        if($inpatient_records == 'discharges' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->discharges_male, "Female" => $value->discharges_female, "Total" => $value->discharges_total);
                        }
                        if($inpatient_records == 'death' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->death_male, "Female" =>$value->death_female, "Total" => $value->death_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($inpatient_records == '' || $inpatient_records == 'admission' ){
                        $chartData['Admission'] = array("Male" => $data->admission_male, "Female" => $data->admission_female, "Total" => $data->admission_total);
                    }
                    if($inpatient_records == '' || $inpatient_records == 'discharges' ){
                        $chartData['Discharges'] = array("Male" => $data->discharges_male, "Female" => $data->discharges_female, "Total" => $data->discharges_total);
                    }
                    if($inpatient_records == '' || $inpatient_records == 'death' ){
                        $chartData['Death'] = array("Male" => $data->death_male, "Female" =>$data->death_female, "Total" => $data->death_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                }  
        }


           return view('reports.inpatient-records', [
                        'data' => $data,
                        'years' => $years,
                        'month_from' => '',
                        'hospital_display_name'=>$hospital_display_name,
                        'hospital_name' => $hospital_name,
                        'hospitalname' => $hospitalname1,
                        'inpatient_records' => $inpatient_records,
                        'postData' => $postData,
                        'labelArray' => $labelArray,
                        'chartFinalData' => $chartFinalData,
                        'piechartData' => $piechartData,
                        'pieChartColor' => $pieChartColor            
                    ]);
    }


    public function InpatientRecordsChart(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $inpatient_records = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){ 
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                if($hospital_name2 != '' && count($hospital_name2) > 0){
                    foreach ($hospital_name2 as $value) {
                        $hospital_name3[]= $value->id;
                    }
                }
            } 
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['inpatient_records'] != '')
            $inpatient_records =$postData['inpatient_records']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $inpatient_records ){
                   $data = InpatientRecords::
                           select(DB::raw('SUM(admission_male) as admission_male,
                                                    SUM(admission_female) as admission_female,
                                                    SUM(admission_total) as admission_total,
                                                    SUM(discharges_male) as discharges_male,
                                                    SUM(discharges_female) as discharges_female,
                                                    SUM(discharges_total) as discharges_total,
                                                    SUM(death_male) as death_male,
                                                    SUM(death_female) as death_female,
                                                    SUM(death_total) as death_total,added_by'))                    
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                    ->groupBy('added_by')
                    ->get();
                  
                    foreach($data as $key => $value1){ 
                        $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name; 
                    }                   
               }else{          
                    $data = InpatientRecords::select(DB::raw('SUM(admission_male) as admission_male,SUM(admission_total) as admission_total,SUM(discharges_male) as discharges_male,SUM(discharges_female) as discharges_female,SUM(discharges_total) as discharges_total,SUM(death_male) as death_male,SUM(death_female) as death_female,SUM(death_total) as death_total'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                if($hospitalname1 != ''){
                                    $q->whereIn('added_by', $hospital_name3);
                                }
                            })
                            ->first(); 
               }
           }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name = Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        
       

        return view('reports.inpatient-records-chart', [
                    'data' => $data,
                    'years' => $years,
                    'hospital_display_name'=>$hospital_display_name,
                    'hospital_name' => $hospital_name,
                    'hospitalname' => $hospitalname1,
                    'inpatient_records' => $inpatient_records,
                    'postData' => $postData]);
    }

    public function tbHiv(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        

           if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                        $data = TBHiv::select(DB::raw('SUM(individuals_clinically_screened_tb_total) as individuals_clinically_screened_tb_total,
                                                       SUM(individuals_clinically_screened_score1_total) as individuals_clinically_screened_score1_total,
                                                       SUM(registered_tb_patients_for_hiv_total) as registered_tb_patients_for_hiv_total,
                                                       SUM(individuals_started_tb_treatment_hiv_negative_total) as individuals_started_tb_treatment_hiv_negative_total,
                                                       SUM(individuals_started_tb_treatment_hiv_unknown_total) as individuals_started_tb_treatment_hiv_unknown_total,
                                                       SUM(	hiv_positive_clients_attending_hiv_total) as 	hiv_positive_clients_attending_hiv_total,
                                                       SUM(tb_patients_with_hiv_receiving_art_total) as tb_patients_with_hiv_receiving_art_total,
                                                       SUM(co_infected_persons_on_cpt_total) as co_infected_persons_on_cpt_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->first();
                }else{
                        $data = TBHiv::select(DB::raw('SUM(individuals_clinically_screened_tb_total) as individuals_clinically_screened_tb_total,
                                                       SUM(individuals_clinically_screened_score1_total) as individuals_clinically_screened_score1_total,
                                                       SUM(registered_tb_patients_for_hiv_total) as registered_tb_patients_for_hiv_total,
                                                       SUM(individuals_started_tb_treatment_hiv_negative_total) as individuals_started_tb_treatment_hiv_negative_total,
                                                       SUM(individuals_started_tb_treatment_hiv_unknown_total) as individuals_started_tb_treatment_hiv_unknown_total,
                                                       SUM(	hiv_positive_clients_attending_hiv_total) as 	hiv_positive_clients_attending_hiv_total,
                                                       SUM(tb_patients_with_hiv_receiving_art_total) as tb_patients_with_hiv_receiving_art_total,
                                                       SUM(co_infected_persons_on_cpt_total) as co_infected_persons_on_cpt_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                                ->first();
                }
            
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.tb-hiv', [
            'data' => $data,
            'years' => $years,
             'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }

    public function pmtctMother(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                        $data = PMTCTMother::select(DB::raw('SUM(pregnant_women_tested_hiv_positive_total) as pregnant_women_tested_hiv_positive_total,
                                                       SUM(anc_women_with_previously_hiv_status_total) as anc_women_with_previously_hiv_status_total,
                                                       SUM(	pregnant_women_testing_received_results_anc_total) as 	pregnant_women_testing_received_results_anc_total,
                                                       SUM(	pregnant_women_testing_received_results_l_d_total) as 	pregnant_women_testing_received_results_l_d_total,
                                                       SUM(pregnant_women_testing_received_results_pnc_total) as pregnant_women_testing_received_results_pnc_total,
                                                       SUM(partners_hiv_positive_pregnant_women_tested_hiv_negative) as partners_hiv_positive_pregnant_women_tested_hiv_negative,
                                                       SUM(	partners_hiv_positive_pregnant_women_tested_hiv_positive) as 	partners_hiv_positive_pregnant_women_tested_hiv_positive,
                                                       SUM(	partners_hiv_negative_pregnant_women_tested_hiv_positive) as 	partners_hiv_negative_pregnant_women_tested_hiv_positive,
                                                       SUM(partners_hiv_negative_pregnant_women_tested_hiv_negative) as partners_hiv_negative_pregnant_women_tested_hiv_negative,
                                                       SUM(hiv_positive_pregnant_women_art_eligibility_stage_cd4) as hiv_positive_pregnant_women_art_eligibility_stage_cd4,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour,
                                                       SUM(	pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt) as 	pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->with('addedBy')
                                ->first();
               }else{
                    $data = PMTCTMother::select(DB::raw('SUM(pregnant_women_tested_hiv_positive_total) as pregnant_women_tested_hiv_positive_total,
                                                       SUM(anc_women_with_previously_hiv_status_total) as anc_women_with_previously_hiv_status_total,
                                                       SUM(	pregnant_women_testing_received_results_anc_total) as 	pregnant_women_testing_received_results_anc_total,
                                                       SUM(	pregnant_women_testing_received_results_l_d_total) as 	pregnant_women_testing_received_results_l_d_total,
                                                       SUM(pregnant_women_testing_received_results_pnc_total) as pregnant_women_testing_received_results_pnc_total,
                                                       SUM(partners_hiv_positive_pregnant_women_tested_hiv_negative) as partners_hiv_positive_pregnant_women_tested_hiv_negative,
                                                       SUM(	partners_hiv_positive_pregnant_women_tested_hiv_positive) as 	partners_hiv_positive_pregnant_women_tested_hiv_positive,
                                                       SUM(	partners_hiv_negative_pregnant_women_tested_hiv_positive) as 	partners_hiv_negative_pregnant_women_tested_hiv_positive,
                                                       SUM(partners_hiv_negative_pregnant_women_tested_hiv_negative) as partners_hiv_negative_pregnant_women_tested_hiv_negative,
                                                       SUM(hiv_positive_pregnant_women_art_eligibility_stage_cd4) as hiv_positive_pregnant_women_art_eligibility_stage_cd4,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_triple,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour,
                                                       SUM(	pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt) as 	pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_azt,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_sdnvp_labour_o,
                                                       SUM(pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total) as pregnant_hiv_positive_woman_arv_prophylaxis_pmtct_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                                ->with('addedBy')
                                ->first();
                }
            
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.pmtct-mother', [
            'data' => $data,
            'years' => $years,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }
    
    public function tbLpReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                                $data = TbLpForm::select(DB::raw('SUM(tb_cases_notified_0to4yrs_male) as tb_cases_notified_0to4yrs_male,
                                           SUM(tb_cases_notified_5to15yrs_male) as tb_cases_notified_5to15yrs_male,
                                           SUM(	tb_cases_notified_gt15yrs_male) as tb_cases_notified_gt15yrs_male,
                                           SUM(	tb_cases_notified_0to4yrs_female) as 	tb_cases_notified_0to4yrs_female,
                                           SUM(tb_cases_notified_5to15yrs_female) as tb_cases_notified_5to15yrs_female,
                                           SUM(tb_cases_notified_gt15yrs_female) as tb_cases_notified_gt15yrs_female,
                                           SUM(	tb_cases_notified_total) as tb_cases_notified_total,
                                           SUM(	tb_cases_successfully_treated_0to4yrs_male) as 	tb_cases_successfully_treated_0to4yrs_male,
                                           SUM(tb_cases_successfully_treated_5to15yrs_male) as tb_cases_successfully_treated_5to15yrs_male,
                                           SUM(tb_cases_successfully_treated_gt15yrs_male) as tb_cases_successfully_treated_gt15yrs_male,
                                           SUM(tb_cases_successfully_treated_0to4yrs_female) as tb_cases_successfully_treated_0to4yrs_female,
                                           SUM(tb_cases_successfully_treated_5to15yrs_female) as tb_cases_successfully_treated_5to15yrs_female,
                                           SUM(tb_cases_successfully_treated_gt15yrs_female) as tb_cases_successfully_treated_gt15yrs_female,
                                           SUM(tb_cases_successfully_treated_total) as tb_cases_successfully_treated_total,
                                           SUM(individual_suspects_screened_tb_0to4yrs_male) as individual_suspects_screened_tb_0to4yrs_male,
                                           SUM(	individual_suspects_screened_tb_5to15yrs_male) as individual_suspects_screened_tb_5to15yrs_male,
                                           SUM(individual_suspects_screened_tb_gt15yrs_male) as individual_suspects_screened_tb_gt15yrs_male,
                                           SUM(individual_suspects_screened_tb_0to4yrs_female) as individual_suspects_screened_tb_0to4yrs_female,
                                           SUM(individual_suspects_screened_tb_5to15yrs_female) as individual_suspects_screened_tb_5to15yrs_female,
                                           SUM(individual_suspects_screened_tb_gt15yrs_female) as individual_suspects_screened_tb_gt15yrs_female,
                                           SUM(individual_suspects_screened_tb_total) as individual_suspects_screened_tb_total,
                                           SUM(drtb_suspects_tested_0to4yrs_male) as drtb_suspects_tested_0to4yrs_male,
                                           SUM(drtb_suspects_tested_5to15yrs_male) as drtb_suspects_tested_5to15yrs_male,
                                           SUM(drtb_suspects_tested_gt15yrs_male) as drtb_suspects_tested_gt15yrs_male,
                                           SUM(drtb_suspects_tested_0to4yrs_female) as drtb_suspects_tested_0to4yrs_female,
                                           SUM(drtb_suspects_tested_5to15yrs_female) as drtb_suspects_tested_5to15yrs_female,
                                           SUM(drtb_suspects_tested_gt15yrs_female) as drtb_suspects_tested_gt15yrs_female,
                                           SUM(drtb_suspects_tested_total) as drtb_suspects_tested_total,
                                           SUM(	conf_drtb_pt_enrolled_trt_0to4yrs_male) as 	conf_drtb_pt_enrolled_trt_0to4yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_5to15yrs_male) as conf_drtb_pt_enrolled_trt_5to15yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_gt15yrs_male) as conf_drtb_pt_enrolled_trt_gt15yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_0to4yrs_female) as conf_drtb_pt_enrolled_trt_0to4yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_5to15yrs_female) as conf_drtb_pt_enrolled_trt_5to15yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_gt15yrs_female) as conf_drtb_pt_enrolled_trt_gt15yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_total) as conf_drtb_pt_enrolled_trt_total,
                                           SUM(leprosy_case_registered_0to4yrs_male) as leprosy_case_registered_0to4yrs_male,
                                           SUM(	leprosy_case_registered_5to15yrs_male) as leprosy_case_registered_5to15yrs_male,
                                           SUM(leprosy_case_registered_gt15yrs_male) as leprosy_case_registered_gt15yrs_male,
                                           SUM(leprosy_case_registered_0to4yrs_female) as leprosy_case_registered_0to4yrs_female,
                                           SUM(leprosy_case_registered_5to15yrs_female) as leprosy_case_registered_5to15yrs_female,
                                           SUM(leprosy_case_registered_gt15yrs_female) as leprosy_case_registered_gt15yrs_female,
                                           SUM(leprosy_case_registered_total) as leprosy_case_registered_total,
                                           SUM(	buruli_ulcer_pt_notified_0to4yrs_male) as buruli_ulcer_pt_notified_0to4yrs_male,
                                           SUM(	buruli_ulcer_pt_notified_5to15yrs_male) as buruli_ulcer_pt_notified_5to15yrs_male,
                                           SUM(buruli_ulcer_pt_notified_gt15yrs_male) as buruli_ulcer_pt_notified_gt15yrs_male,
                                           SUM(buruli_ulcer_pt_notified_0to4yrs_female) as buruli_ulcer_pt_notified_0to4yrs_female,
                                           SUM(buruli_ulcer_pt_notified_5to15yrs_female) as buruli_ulcer_pt_notified_5to15yrs_female,
                                           SUM(buruli_ulcer_pt_notified_gt15yrs_female) as buruli_ulcer_pt_notified_gt15yrs_female,
                                           SUM(buruli_ulcer_pt_notified_total) as buruli_ulcer_pt_notified_total
                                        '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                    
                    ->first();
               }else{
                   $data = TbLpForm::select(DB::raw('SUM(tb_cases_notified_0to4yrs_male) as tb_cases_notified_0to4yrs_male,
                                           SUM(tb_cases_notified_5to15yrs_male) as tb_cases_notified_5to15yrs_male,
                                           SUM(	tb_cases_notified_gt15yrs_male) as tb_cases_notified_gt15yrs_male,
                                           SUM(	tb_cases_notified_0to4yrs_female) as 	tb_cases_notified_0to4yrs_female,
                                           SUM(tb_cases_notified_5to15yrs_female) as tb_cases_notified_5to15yrs_female,
                                           SUM(tb_cases_notified_gt15yrs_female) as tb_cases_notified_gt15yrs_female,
                                           SUM(	tb_cases_notified_total) as tb_cases_notified_total,
                                           SUM(	tb_cases_successfully_treated_0to4yrs_male) as 	tb_cases_successfully_treated_0to4yrs_male,
                                           SUM(tb_cases_successfully_treated_5to15yrs_male) as tb_cases_successfully_treated_5to15yrs_male,
                                           SUM(tb_cases_successfully_treated_gt15yrs_male) as tb_cases_successfully_treated_gt15yrs_male,
                                           SUM(tb_cases_successfully_treated_0to4yrs_female) as tb_cases_successfully_treated_0to4yrs_female,
                                           SUM(tb_cases_successfully_treated_5to15yrs_female) as tb_cases_successfully_treated_5to15yrs_female,
                                           SUM(tb_cases_successfully_treated_gt15yrs_female) as tb_cases_successfully_treated_gt15yrs_female,
                                           SUM(tb_cases_successfully_treated_total) as tb_cases_successfully_treated_total,
                                           SUM(individual_suspects_screened_tb_0to4yrs_male) as individual_suspects_screened_tb_0to4yrs_male,
                                           SUM(	individual_suspects_screened_tb_5to15yrs_male) as individual_suspects_screened_tb_5to15yrs_male,
                                           SUM(individual_suspects_screened_tb_gt15yrs_male) as individual_suspects_screened_tb_gt15yrs_male,
                                           SUM(individual_suspects_screened_tb_0to4yrs_female) as individual_suspects_screened_tb_0to4yrs_female,
                                           SUM(individual_suspects_screened_tb_5to15yrs_female) as individual_suspects_screened_tb_5to15yrs_female,
                                           SUM(individual_suspects_screened_tb_gt15yrs_female) as individual_suspects_screened_tb_gt15yrs_female,
                                           SUM(individual_suspects_screened_tb_total) as individual_suspects_screened_tb_total,
                                           SUM(drtb_suspects_tested_0to4yrs_male) as drtb_suspects_tested_0to4yrs_male,
                                           SUM(drtb_suspects_tested_5to15yrs_male) as drtb_suspects_tested_5to15yrs_male,
                                           SUM(drtb_suspects_tested_gt15yrs_male) as drtb_suspects_tested_gt15yrs_male,
                                           SUM(drtb_suspects_tested_0to4yrs_female) as drtb_suspects_tested_0to4yrs_female,
                                           SUM(drtb_suspects_tested_5to15yrs_female) as drtb_suspects_tested_5to15yrs_female,
                                           SUM(drtb_suspects_tested_gt15yrs_female) as drtb_suspects_tested_gt15yrs_female,
                                           SUM(drtb_suspects_tested_total) as drtb_suspects_tested_total,
                                           SUM(	conf_drtb_pt_enrolled_trt_0to4yrs_male) as 	conf_drtb_pt_enrolled_trt_0to4yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_5to15yrs_male) as conf_drtb_pt_enrolled_trt_5to15yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_gt15yrs_male) as conf_drtb_pt_enrolled_trt_gt15yrs_male,
                                           SUM(conf_drtb_pt_enrolled_trt_0to4yrs_female) as conf_drtb_pt_enrolled_trt_0to4yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_5to15yrs_female) as conf_drtb_pt_enrolled_trt_5to15yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_gt15yrs_female) as conf_drtb_pt_enrolled_trt_gt15yrs_female,
                                           SUM(conf_drtb_pt_enrolled_trt_total) as conf_drtb_pt_enrolled_trt_total,
                                           SUM(leprosy_case_registered_0to4yrs_male) as leprosy_case_registered_0to4yrs_male,
                                           SUM(	leprosy_case_registered_5to15yrs_male) as leprosy_case_registered_5to15yrs_male,
                                           SUM(leprosy_case_registered_gt15yrs_male) as leprosy_case_registered_gt15yrs_male,
                                           SUM(leprosy_case_registered_0to4yrs_female) as leprosy_case_registered_0to4yrs_female,
                                           SUM(leprosy_case_registered_5to15yrs_female) as leprosy_case_registered_5to15yrs_female,
                                           SUM(leprosy_case_registered_gt15yrs_female) as leprosy_case_registered_gt15yrs_female,
                                           SUM(leprosy_case_registered_total) as leprosy_case_registered_total,
                                           SUM(	buruli_ulcer_pt_notified_0to4yrs_male) as buruli_ulcer_pt_notified_0to4yrs_male,
                                           SUM(	buruli_ulcer_pt_notified_5to15yrs_male) as buruli_ulcer_pt_notified_5to15yrs_male,
                                           SUM(buruli_ulcer_pt_notified_gt15yrs_male) as buruli_ulcer_pt_notified_gt15yrs_male,
                                           SUM(buruli_ulcer_pt_notified_0to4yrs_female) as buruli_ulcer_pt_notified_0to4yrs_female,
                                           SUM(buruli_ulcer_pt_notified_5to15yrs_female) as buruli_ulcer_pt_notified_5to15yrs_female,
                                           SUM(buruli_ulcer_pt_notified_gt15yrs_female) as buruli_ulcer_pt_notified_gt15yrs_female,
                                           SUM(buruli_ulcer_pt_notified_total) as buruli_ulcer_pt_notified_total
                                        '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                           //->where('year', $postData['year'])

                     ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                    ->first();
               }
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
        return view('reports.tb-lp', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }
    
    public function pmtctInfantReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();
        
         $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }

           if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                        $data = PmctcInfantForm::select(DB::raw('SUM(infants_born_hiv_infected_women_cp_within2m_male) as infants_born_hiv_infected_women_cp_within2m_male,
                                                       SUM(infants_born_hiv_infected_women_cp_within2m_female) as infants_born_hiv_infected_women_cp_within2m_female,
                                                       SUM(	infants_born_hiv_infected_women_cp_within2m_total) as 	infants_born_hiv_infected_women_cp_within2m_total,
                                                       SUM(	infants_born_hiv_infected_women_cp_2mabouve_male) as 	infants_born_hiv_infected_women_cp_2mabouve_male,
                                                       SUM(infants_born_hiv_infected_women_cp_2mabouve_female) as infants_born_hiv_infected_women_cp_2mabouve_female,
                                                       SUM(infants_born_hiv_infected_women_cp_2mabouve_total) as infants_born_hiv_infected_women_cp_2mabouve_total,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_male) as 	infants_born_hiv_infected_women_received_hivtest_within2m_male,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_female) as 	infants_born_hiv_infected_women_received_hivtest_within2m_female,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_total) as 	infants_born_hiv_infected_women_received_hivtest_within2m_total,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_male) as infants_born_hiv_infected_women_received_hivtest_after2m_male,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_female) as infants_born_hiv_infected_women_received_hivtest_after2m_female,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_total) as infants_born_hiv_infected_women_received_hivtest_after2m_total,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_at18m_male) as 	infants_born_hiv_infected_women_received_hivtest_at18m_male,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_at18m_female) as infants_born_hiv_infected_women_received_hivtest_at18m_female,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_at18m_total) as infants_born_hiv_infected_women_received_hivtest_at18m_total,
                                                       SUM(hiv_infected_women_negative_hivrapidtest_at18m_male) as hiv_infected_women_negative_hivrapidtest_at18m_male,
                                                       SUM(hiv_infected_women_negative_hivrapidtest_at18m_female) as hiv_infected_women_negative_hivrapidtest_at18m_female,
                                                       SUM(	hiv_infected_women_negative_hivrapidtest_at18m_total) as 	hiv_infected_women_negative_hivrapidtest_at18m_total,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_male) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_male,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_female) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_female,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_total) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->first();
                }else{
                    $data = PmctcInfantForm::select(DB::raw('SUM(infants_born_hiv_infected_women_cp_within2m_male) as infants_born_hiv_infected_women_cp_within2m_male,
                                                       SUM(infants_born_hiv_infected_women_cp_within2m_female) as infants_born_hiv_infected_women_cp_within2m_female,
                                                       SUM(	infants_born_hiv_infected_women_cp_within2m_total) as 	infants_born_hiv_infected_women_cp_within2m_total,
                                                       SUM(	infants_born_hiv_infected_women_cp_2mabouve_male) as 	infants_born_hiv_infected_women_cp_2mabouve_male,
                                                       SUM(infants_born_hiv_infected_women_cp_2mabouve_female) as infants_born_hiv_infected_women_cp_2mabouve_female,
                                                       SUM(infants_born_hiv_infected_women_cp_2mabouve_total) as infants_born_hiv_infected_women_cp_2mabouve_total,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_male) as 	infants_born_hiv_infected_women_received_hivtest_within2m_male,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_female) as 	infants_born_hiv_infected_women_received_hivtest_within2m_female,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_within2m_total) as 	infants_born_hiv_infected_women_received_hivtest_within2m_total,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_male) as infants_born_hiv_infected_women_received_hivtest_after2m_male,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_female) as infants_born_hiv_infected_women_received_hivtest_after2m_female,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_after2m_total) as infants_born_hiv_infected_women_received_hivtest_after2m_total,
                                                       SUM(	infants_born_hiv_infected_women_received_hivtest_at18m_male) as 	infants_born_hiv_infected_women_received_hivtest_at18m_male,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_at18m_female) as infants_born_hiv_infected_women_received_hivtest_at18m_female,
                                                       SUM(infants_born_hiv_infected_women_received_hivtest_at18m_total) as infants_born_hiv_infected_women_received_hivtest_at18m_total,
                                                       SUM(hiv_infected_women_negative_hivrapidtest_at18m_male) as hiv_infected_women_negative_hivrapidtest_at18m_male,
                                                       SUM(hiv_infected_women_negative_hivrapidtest_at18m_female) as hiv_infected_women_negative_hivrapidtest_at18m_female,
                                                       SUM(	hiv_infected_women_negative_hivrapidtest_at18m_total) as 	hiv_infected_women_negative_hivrapidtest_at18m_total,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_male) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_male,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_female) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_female,
                                                       SUM(hiv_exposed_ibf_and_receiving_arv_prophylaxis_total) as hiv_exposed_ibf_and_receiving_arv_prophylaxis_total
                                                    '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                       //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                                ->first();
                }
            
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.pmtct-infant', [
            'data' => $data,
            'years' => $years,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'postData' => $postData]);
    }
    
    public function hivTbMalariaServicesReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = HivTbMalariaIntegratedServices::select(DB::raw('SUM(hiv_counselled_tested_lt2yrs_male) as hiv_counselled_tested_lt2yrs_male,
                                                   SUM(hiv_counselled_tested_2to14yrs_male) as hiv_counselled_tested_2to14yrs_male,
                                                   SUM(	hiv_counselled_tested_15to19yrs_male) as hiv_counselled_tested_15to19yrs_male,
                                                   SUM(	hiv_counselled_tested_20to24yrs_male) as hiv_counselled_tested_20to24yrs_male,
                                                   SUM(hiv_counselled_tested_25to49yrs_male) as hiv_counselled_tested_25to49yrs_male,
                                                   SUM(hiv_counselled_tested_plus50yrs_male) as hiv_counselled_tested_plus50yrs_male,
                                                   SUM(	hiv_counselled_tested_lt2yrs_female) as hiv_counselled_tested_lt2yrs_female,
                                                   SUM(	hiv_counselled_tested_2to14yrs_female) as hiv_counselled_tested_2to14yrs_female,
                                                   SUM(	hiv_counselled_tested_15to19yrs_female) as hiv_counselled_tested_15to19yrs_female,
                                                   SUM(hiv_counselled_tested_20to24yrs_female) as hiv_counselled_tested_20to24yrs_female,
                                                   SUM(	hiv_counselled_tested_25to49yrs_female) as hiv_counselled_tested_25to49yrs_female,
                                                   SUM(	hiv_counselled_tested_plus50yrs_female) as 	hiv_counselled_tested_plus50yrs_female,
                                                   SUM(	hiv_counselled_tested_total) as hiv_counselled_tested_total,
                                                   SUM(	hiv_tested_positive_lt2yrs_male) as 	hiv_tested_positive_lt2yrs_male,
                                                   SUM(hiv_tested_positive_2to14yrs_male) as hiv_tested_positive_2to14yrs_male,
                                                   SUM(hiv_tested_positive_15to19yrs_male) as hiv_tested_positive_15to19yrs_male,
                                                   SUM(hiv_tested_positive_20to24yrs_male) as hiv_tested_positive_20to24yrs_male,
                                                   SUM(	hiv_tested_positive_25to49yrs_male) as 	hiv_tested_positive_25to49yrs_male,
                                                   SUM(hiv_tested_positive_plus50yrs_male) as hiv_tested_positive_plus50yrs_male,
                                                   SUM(hiv_tested_positive_lt2yrs_female) as hiv_tested_positive_lt2yrs_female,
                                                   SUM(	hiv_tested_positive_2to14yrs_female) as hiv_tested_positive_2to14yrs_female,
                                                   SUM(	hiv_tested_positive_15to19yrs_female) as hiv_tested_positive_15to19yrs_female,
                                                   SUM(	hiv_tested_positive_20to24yrs_female) as hiv_tested_positive_20to24yrs_female,
                                                   SUM(	hiv_tested_positive_25to49yrs_female) as hiv_tested_positive_25to49yrs_female,
                                                   SUM(	hiv_tested_positive_plus50yrs_female) as hiv_tested_positive_plus50yrs_female,
                                                   SUM(	hiv_tested_positive_total) as hiv_tested_positive_total,
                                                   SUM(	couples_hiv_counselled_tested_total) as couples_hiv_counselled_tested_total,
                                                   SUM(	couples_hiv_counselled_tested_sero_discordant_total) as couples_hiv_counselled_tested_sero_discordant_total,
                                                   SUM(	hiv_pt_rc_cp_ls15yrs_male) as hiv_pt_rc_cp_ls15yrs_male,
                                                   SUM(	hiv_pt_rc_cp_gteq15yrs_male) as hiv_pt_rc_cp_gteq15yrs_male,
                                                   SUM(	hiv_pt_rc_cp_ls15yrs_female) as hiv_pt_rc_cp_ls15yrs_female,
                                                   SUM(	hiv_pt_rc_cp_gteq15yrs_female) as hiv_pt_rc_cp_gteq15yrs_female,
                                                   SUM(	hiv_pt_rc_cp_total) as hiv_pt_rc_cp_total,
                                                   SUM(	art_pt_rc_arv_refill_ls15yrs_male) as art_pt_rc_arv_refill_ls15yrs_male,
                                                   SUM(	art_pt_rc_arv_refill_gteq15yrs_male) as art_pt_rc_arv_refill_gteq15yrs_male,
                                                   SUM(	art_pt_rc_arv_refill_ls15yrs_female) as art_pt_rc_arv_refill_ls15yrs_female,
                                                   SUM(	art_pt_rc_arv_refill_gteq15yrs_female) as 	art_pt_rc_arv_refill_gteq15yrs_female,
                                                   SUM(	art_pt_rc_arv_refill_total) as 	art_pt_rc_arv_refill_total
                                                '))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->first();
               }else{
                   $data = HivTbMalariaIntegratedServices::select(DB::raw('SUM(hiv_counselled_tested_lt2yrs_male) as hiv_counselled_tested_lt2yrs_male,
                                                   SUM(hiv_counselled_tested_2to14yrs_male) as hiv_counselled_tested_2to14yrs_male,
                                                   SUM(	hiv_counselled_tested_15to19yrs_male) as hiv_counselled_tested_15to19yrs_male,
                                                   SUM(	hiv_counselled_tested_20to24yrs_male) as hiv_counselled_tested_20to24yrs_male,
                                                   SUM(hiv_counselled_tested_25to49yrs_male) as hiv_counselled_tested_25to49yrs_male,
                                                   SUM(hiv_counselled_tested_plus50yrs_male) as hiv_counselled_tested_plus50yrs_male,
                                                   SUM(	hiv_counselled_tested_lt2yrs_female) as hiv_counselled_tested_lt2yrs_female,
                                                   SUM(	hiv_counselled_tested_2to14yrs_female) as hiv_counselled_tested_2to14yrs_female,
                                                   SUM(	hiv_counselled_tested_15to19yrs_female) as hiv_counselled_tested_15to19yrs_female,
                                                   SUM(hiv_counselled_tested_20to24yrs_female) as hiv_counselled_tested_20to24yrs_female,
                                                   SUM(	hiv_counselled_tested_25to49yrs_female) as hiv_counselled_tested_25to49yrs_female,
                                                   SUM(	hiv_counselled_tested_plus50yrs_female) as 	hiv_counselled_tested_plus50yrs_female,
                                                   SUM(	hiv_counselled_tested_total) as hiv_counselled_tested_total,
                                                   SUM(	hiv_tested_positive_lt2yrs_male) as 	hiv_tested_positive_lt2yrs_male,
                                                   SUM(hiv_tested_positive_2to14yrs_male) as hiv_tested_positive_2to14yrs_male,
                                                   SUM(hiv_tested_positive_15to19yrs_male) as hiv_tested_positive_15to19yrs_male,
                                                   SUM(hiv_tested_positive_20to24yrs_male) as hiv_tested_positive_20to24yrs_male,
                                                   SUM(	hiv_tested_positive_25to49yrs_male) as 	hiv_tested_positive_25to49yrs_male,
                                                   SUM(hiv_tested_positive_plus50yrs_male) as hiv_tested_positive_plus50yrs_male,
                                                   SUM(hiv_tested_positive_lt2yrs_female) as hiv_tested_positive_lt2yrs_female,
                                                   SUM(	hiv_tested_positive_2to14yrs_female) as hiv_tested_positive_2to14yrs_female,
                                                   SUM(	hiv_tested_positive_15to19yrs_female) as hiv_tested_positive_15to19yrs_female,
                                                   SUM(	hiv_tested_positive_20to24yrs_female) as hiv_tested_positive_20to24yrs_female,
                                                   SUM(	hiv_tested_positive_25to49yrs_female) as hiv_tested_positive_25to49yrs_female,
                                                   SUM(	hiv_tested_positive_plus50yrs_female) as hiv_tested_positive_plus50yrs_female,
                                                   SUM(	hiv_tested_positive_total) as hiv_tested_positive_total,
                                                   SUM(	couples_hiv_counselled_tested_total) as couples_hiv_counselled_tested_total,
                                                   SUM(	couples_hiv_counselled_tested_sero_discordant_total) as couples_hiv_counselled_tested_sero_discordant_total,
                                                   SUM(	hiv_pt_rc_cp_ls15yrs_male) as hiv_pt_rc_cp_ls15yrs_male,
                                                   SUM(	hiv_pt_rc_cp_gteq15yrs_male) as hiv_pt_rc_cp_gteq15yrs_male,
                                                   SUM(	hiv_pt_rc_cp_ls15yrs_female) as hiv_pt_rc_cp_ls15yrs_female,
                                                   SUM(	hiv_pt_rc_cp_gteq15yrs_female) as hiv_pt_rc_cp_gteq15yrs_female,
                                                   SUM(	hiv_pt_rc_cp_total) as hiv_pt_rc_cp_total,
                                                   SUM(	art_pt_rc_arv_refill_ls15yrs_male) as art_pt_rc_arv_refill_ls15yrs_male,
                                                   SUM(	art_pt_rc_arv_refill_gteq15yrs_male) as art_pt_rc_arv_refill_gteq15yrs_male,
                                                   SUM(	art_pt_rc_arv_refill_ls15yrs_female) as art_pt_rc_arv_refill_ls15yrs_female,
                                                   SUM(	art_pt_rc_arv_refill_gteq15yrs_female) as 	art_pt_rc_arv_refill_gteq15yrs_female,
                                                   SUM(	art_pt_rc_arv_refill_total) as 	art_pt_rc_arv_refill_total
                                                '))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        return view('reports.hiv-tb-malaria-services', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData
        ]);
    }
    
    public function AccidentEmergency(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $accident_emergencys = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){

            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['accident_emergencys'] != '')
            $accident_emergencys =$postData['accident_emergencys']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) { 
                if($hospitalname1 == '' && $accident_emergencys != ''){
                   $data = AccidentEmergency::select(DB::raw('SUM(rta_cases_male) as rta_cases_male,
                                                    SUM(rta_cases_female) as rta_cases_female,
                                                    SUM(rta_cases_total) as rta_cases_total,
                                                    SUM(dressing_a_e_male) as dressing_a_e_male,
                                                    SUM(dressing_a_e_female) as dressing_a_e_female,
                                                    SUM(dressing_a_e_total) as dressing_a_e_total,
                                                    SUM(dressing_sopd_male) as dressing_sopd_male,
                                                    SUM(dressing_sopd_female) as dressing_sopd_female,
                                                    SUM(dressing_sopd_total) as dressing_sopd_total,
                                                    SUM(injection_male) as injection_male,
                                                    SUM(injection_female) as injection_female,
                                                    SUM(injection_total) as injection_total,
                                                    SUM(a_e_attendance_male) as a_e_attendance_male,
                                                    SUM(a_e_attendance_female) as a_e_attendance_female,
                                                    SUM(a_e_attendance_total) as a_e_attendance_total,
                                                    SUM(epu_attendance_male) as epu_attendance_male,
                                                    SUM(epu_attendance_female) as epu_attendance_female,
                                                    SUM(epu_attendance_total) as epu_attendance_total,
                                                    SUM(bid_male) as bid_male,
                                                    SUM(bid_female) as bid_female,
                                                    SUM(bid_total) as bid_total,added_by
                                              '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    //                        //->where('year', $postData['year'])

                    ->groupBy('added_by')
                    ->get();
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                      
                   
               }else{
                        $data = AccidentEmergency::select(DB::raw('SUM(rta_cases_male) as rta_cases_male,
                                                                SUM(rta_cases_female) as rta_cases_female,
                                                                SUM(rta_cases_total) as rta_cases_total,
                                                                SUM(dressing_a_e_male) as dressing_a_e_male,
                                                                SUM(dressing_a_e_female) as dressing_a_e_female,
                                                                SUM(dressing_a_e_total) as dressing_a_e_total,
                                                                SUM(dressing_sopd_male) as dressing_sopd_male,
                                                                SUM(dressing_sopd_female) as dressing_sopd_female,
                                                                SUM(dressing_sopd_total) as dressing_sopd_total,
                                                                SUM(injection_male) as injection_male,
                                                                SUM(injection_female) as injection_female,
                                                                SUM(injection_total) as injection_total,
                                                                SUM(a_e_attendance_male) as a_e_attendance_male,
                                                                SUM(a_e_attendance_female) as a_e_attendance_female,
                                                                SUM(a_e_attendance_total) as a_e_attendance_total,
                                                                SUM(epu_attendance_male) as epu_attendance_male,
                                                                SUM(epu_attendance_female) as epu_attendance_female,
                                                                SUM(epu_attendance_total) as epu_attendance_total,
                                                                SUM(bid_male) as bid_male,
                                                                SUM(bid_female) as bid_female,
                                                                SUM(bid_total) as bid_total
                                                          '))
                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                //                        //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                                ->first();
            
                }
           }
            $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
 
        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $accident_emergencys != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($accident_emergencys == '' || $accident_emergencys == 'rta_cases' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->rta_cases_male, "Female" =>$value->rta_cases_female, "Total" => $value->rta_cases_total);
                        }
                        if($accident_emergencys == '' || $accident_emergencys == 'dressing_a_e' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->dressing_a_e_male, "Female" =>$value->dressing_a_e_female, "Total" => $value->dressing_a_e_total);
                        }
                        if($accident_emergencys == '' || $accident_emergencys == 'injection' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->injection_male, "Female" =>$value->injection_female, "Total" => $value->injection_total);
                        }
                        if($accident_emergencys == '' || $accident_emergencys == 'bid' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->bid_male, "Female" =>$value->bid_female, "Total" => $value->bid_total);
                        }
                        if($accident_emergencys == '' || $accident_emergencys == 'a_e_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->a_e_attendance_male, "Female" =>$value->a_e_attendance_female, "Total" => $value->a_e_attendance_total);
                        }
                        if($accident_emergencys == '' || $accident_emergencys == 'epu_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->epu_attendance_male, "Female" =>$value->epu_attendance_female, "Total" => $value->epu_attendance_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($accident_emergencys == '' || $accident_emergencys == 'rta_cases' ){
                        $chartData['RTA Cases'] = array("Male" => $data->rta_cases_male, "Female" => $data->rta_cases_female, "Total" => $data->rta_cases_total);
                    }
                    if($accident_emergencys == '' || $accident_emergencys == 'dressing_a_e' ){
                        $chartData['Dressing a e'] = array("Male" => $data->dressing_a_e_male, "Female" => $data->dressing_a_e_female, "Total" => $data->dressing_a_e_total);
                    }
                    if($accident_emergencys == '' || $accident_emergencys == 'injection' ){
                        $chartData['Injection'] = array("Male" => $data->injection_male, "Female" =>$data->injection_female, "Total" => $data->injection_total);
                    }
                    if($accident_emergencys == '' || $accident_emergencys == 'bid' ){
                        $chartData['Bid'] = array("Male" => $data->bid_male, "Female" => $data->bid_female, "Total" => $data->bid_total);
                    }
                    if($accident_emergencys == '' || $accident_emergencys == 'a_e_attendance' ){
                        $chartData['A&E Attendance'] = array("Male" => $data->a_e_attendance_male, "Female" => $data->a_e_attendance_female, "Total" => $data->a_e_attendance_total);
                    }
                    if($accident_emergencys == '' || $accident_emergencys == 'epu_attendance' ){
                        $chartData['EPU Attendance'] = array("Male" => $data->epu_attendance_male, "Female" => $data->epu_attendance_female, "Total" => $data->epu_attendance_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                } 
        }

        return view('reports.accident-emergency', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'accident_emergencys' => $accident_emergencys,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function rndRGBColorCode() { 
        return 'rgb(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ')'; 
    }

    public function GeneralOutpatient(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $general_outpatient = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['general_outpatient'] != '')
            $general_outpatient =$postData['general_outpatient']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $general_outpatient != ''){
                   $data = GeneralOutpatient::select(DB::raw('SUM(gopd_attendance_adult_male) as gopd_attendance_adult_male,
                                                    SUM(gopd_attendance_adult_female) as gopd_attendance_adult_female,
                                                    SUM(gopd_attendance_adult_total) as gopd_attendance_adult_total,
                                                    
                                                    SUM(gopd_attendance_pediatrics_male) as gopd_attendance_pediatrics_male,
                                                    SUM(gopd_attendance_pediatrics_female) as gopd_attendance_pediatrics_female,
                                                    SUM(gopd_attendance_pediatrics_total) as gopd_attendance_pediatrics_total,
                                                    
                                                    SUM(medical_corticated_fitness_male) as medical_corticated_fitness_male,
                                                    SUM(medical_corticated_fitness_female) as medical_corticated_fitness_female,
                                                    SUM(medical_corticated_fitness_total) as medical_corticated_fitness_total,
                                                    
                                                    SUM(maternity_leave_male) as maternity_leave_male,
                                                    SUM(maternity_leave_female) as maternity_leave_female,
                                                    SUM(maternity_leave_total) as maternity_leave_total,
                                                    
                                                    SUM(antenatal_attendance_male) as antenatal_attendance_male,
                                                    SUM(antenatal_attendance_female) as antenatal_attendance_female,
                                                    SUM(antenatal_attendance_total) as antenatal_attendance_total,
                                                    
                                                    SUM(postnatal_attendance_male) as postnatal_attendance_male,
                                                    SUM(postnatal_attendance_female) as postnatal_attendance_female,
                                                    SUM(postnatal_attendance_total) as postnatal_attendance_total,
                                                    
                                                    SUM(nhis_male) as nhis_male,
                                                    SUM(nhis_female) as nhis_female,
                                                    SUM(nhis_total) as nhis_total,

                                                    SUM(fhis_male) as fhis_male,
                                                    SUM(fhis_female) as fhis_female,
                                                    SUM(fhis_total) as fhis_total,
                                                    
                                                    SUM(medical_male) as medical_male,
                                                    SUM(medical_female) as medical_female,
                                                    SUM(medical_total) as medical_total,

                                                    SUM(death_male) as death_male,
                                                    SUM(death_female) as death_female,
                                                    SUM(death_total) as death_total,

                                                    SUM(antenatal_attendance_old_male) as antenatal_attendance_old_male,
                                                    SUM(antenatal_attendance_old_female) as antenatal_attendance_old_female,
                                                    SUM(antenatal_attendance_old_total) as antenatal_attendance_old_total,
                                                    
                                                    SUM(family_planning_attendance_male) as family_planning_attendance_male,
                                                    SUM(family_planning_attendance_female) as family_planning_attendance_female,
                                                    SUM(family_planning_attendance_total) as family_planning_attendance_total,hospital_id
                                                    '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                              ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                          ->groupBy('added_by')
                          ->get();
                 
                         foreach($data as $key => $value1){ 
                          //echo '<pre>'; print_r($value1); exit;
                             $name = User::where('id',$value1->added_by)->first();
                             $hospital_name1 = Hospitals::where('id',$value1->hospital_id)->first();
                              $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                                   
                              }
                        
                   
               }else{
                            $data = GeneralOutpatient::select(DB::raw('SUM(gopd_attendance_adult_male) as gopd_attendance_adult_male,
                                                    SUM(gopd_attendance_adult_female) as gopd_attendance_adult_female,
                                                    SUM(gopd_attendance_adult_total) as gopd_attendance_adult_total,
                                                    
                                                    SUM(gopd_attendance_pediatrics_male) as gopd_attendance_pediatrics_male,
                                                    SUM(gopd_attendance_pediatrics_female) as gopd_attendance_pediatrics_female,
                                                    SUM(gopd_attendance_pediatrics_total) as gopd_attendance_pediatrics_total,
                                                    
                                                    SUM(medical_corticated_fitness_male) as medical_corticated_fitness_male,
                                                    SUM(medical_corticated_fitness_female) as medical_corticated_fitness_female,
                                                    SUM(medical_corticated_fitness_total) as medical_corticated_fitness_total,
                                                    
                                                    SUM(maternity_leave_male) as maternity_leave_male,
                                                    SUM(maternity_leave_female) as maternity_leave_female,
                                                    SUM(maternity_leave_total) as maternity_leave_total,
                                                    
                                                    SUM(antenatal_attendance_male) as antenatal_attendance_male,
                                                    SUM(antenatal_attendance_female) as antenatal_attendance_female,
                                                    SUM(antenatal_attendance_total) as antenatal_attendance_total,
                                                    
                                                    SUM(postnatal_attendance_male) as postnatal_attendance_male,
                                                    SUM(postnatal_attendance_female) as postnatal_attendance_female,
                                                    SUM(postnatal_attendance_total) as postnatal_attendance_total,
                                                    
                                                    SUM(nhis_male) as nhis_male,
                                                    SUM(nhis_female) as nhis_female,
                                                    SUM(nhis_total) as nhis_total,

                                                    SUM(fhis_male) as fhis_male,
                                                    SUM(fhis_female) as fhis_female,
                                                    SUM(fhis_total) as fhis_total,
                                                    
                                                    SUM(medical_male) as medical_male,
                                                    SUM(medical_female) as medical_female,
                                                    SUM(medical_total) as medical_total,

                                                    SUM(death_male) as death_male,
                                                    SUM(death_female) as death_female,
                                                    SUM(death_total) as death_total,

                                                    SUM(antenatal_attendance_old_male) as antenatal_attendance_old_male,
                                                    SUM(antenatal_attendance_old_female) as antenatal_attendance_old_female,
                                                    SUM(antenatal_attendance_old_total) as antenatal_attendance_old_total,
                                                    
                                                    SUM(family_planning_attendance_male) as family_planning_attendance_male,
                                                    SUM(family_planning_attendance_female) as family_planning_attendance_female,
                                                    SUM(family_planning_attendance_total) as family_planning_attendance_total
                                              '))
                                              ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                              ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                           if($hospitalname1 != ''){
                                                                $q->whereIn('added_by', $hospital_name3);
                                                           }
                                                        })
                                            ->first();
               }
            
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $general_outpatient != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($general_outpatient == '' || $general_outpatient == 'gopd_attendance_adult' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->gopd_attendance_adult_male, "Female" => $value->gopd_attendance_adult_female, "Total" => $value->gopd_attendance_adult_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'gopd_attendance_pediatrics' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->gopd_attendance_pediatrics_male, "Female" => $value->gopd_attendance_pediatrics_female, "Total" => $value->gopd_attendance_pediatrics_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'medical_corticated_fitness' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->medical_corticated_fitness_male, "Female" =>$value->medical_corticated_fitness_female, "Total" => $value->medical_corticated_fitness_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'maternity_leave' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->maternity_leave_male, "Female" =>$value->maternity_leave_female, "Total" => $value->maternity_leave_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'antenatal_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->antenatal_attendance_male, "Female" => $value->antenatal_attendance_female, "Total" => $value->antenatal_attendance_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'postnatal_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->postnatal_attendance_male, "Female" => $value->postnatal_attendance_female, "Total" => $value->postnatal_attendance_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'nhis' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->nhis_male, "Female" => $value->nhis_female, "Total" => $value->nhis_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'fhis' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->fhis_male, "Female" => $value->fhis_female, "Total" => $value->fhis_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'medical' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->medical_male, "Female" => $value->medical_female, "Total" => $value->medical_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'death' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->death_male, "Female" => $value->death_female, "Total" => $value->death_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'antenatal_attendance_old' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->antenatal_attendance_old_male, "Female" => $value->antenatal_attendance_old_female, "Total" => $value->antenatal_attendance_old_total);
                        }
                        if($general_outpatient == '' || $general_outpatient == 'family_planning_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->family_planning_attendance_male, "Female" => $value->family_planning_attendance_female, "Total" => $value->family_planning_attendance_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($general_outpatient == '' || $general_outpatient == 'gopd_attendance_adult' ){
                        $chartData['GOPD Attendance Adult'] = array("Male" => $data->gopd_attendance_adult_male, "Female" => $data->gopd_attendance_adult_female, "Total" => $data->gopd_attendance_adult_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'gopd_attendance_pediatrics' ){
                        $chartData['Gopd Attendance Pediatrics'] = array("Male" => $data->gopd_attendance_pediatrics_male, "Female" => $data->gopd_attendance_pediatrics_female, "Total" => $data->gopd_attendance_pediatrics_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'medical_corticated_fitness' ){
                        $chartData['Medical Corticated Fitness'] = array("Male" => $data->medical_corticated_fitness_male, "Female" =>$data->medical_corticated_fitness_female, "Total" => $data->medical_corticated_fitness_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'maternity_leave' ){
                        $chartData['Maternity Leave'] = array("Male" => $data->maternity_leave_male, "Female" =>$data->maternity_leave_female, "Total" => $data->maternity_leave_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'antenatal_attendance' ){
                        $chartData['Antenatal Attendance'] = array("Male" => $data->antenatal_attendance_male, "Female" => $data->antenatal_attendance_female, "Total" => $data->antenatal_attendance_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'postnatal_attendance' ){
                        $chartData['Postnatal Attendance'] = array("Male" => $data->postnatal_attendance_male, "Female" => $data->postnatal_attendance_female, "Total" => $data->postnatal_attendance_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'nhis' ){
                        $chartData['National Health Insurance (NHIS)'] = array("Male" => $data->nhis_male, "Female" => $data->nhis_female, "Total" => $data->nhis_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'fhis' ){
                        $chartData['FCT Health Insurance (FHIS)'] = array("Male" => $data->fhis_male, "Female" => $data->fhis_female, "Total" => $data->fhis_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'medical' ){
                        $chartData['Medical Report'] = array("Male" => $data->medical_male, "Female" => $data->medical_female, "Total" => $data->medical_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'death' ){
                        $chartData['Death Certificate'] = array("Male" => $data->death_male, "Female" => $data->death_female, "Total" => $data->death_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'antenatal_attendance_old' ){
                        $chartData['Antenatal Attendance (Old)'] = array("Male" => $data->antenatal_attendance_old_male, "Female" => $data->antenatal_attendance_old_female, "Total" => $data->antenatal_attendance_old_total);
                    }
                    if($general_outpatient == '' || $general_outpatient == 'family_planning_attendance' ){
                        $chartData['Family Planning Attendance'] = array("Male" => $data->family_planning_attendance_male, "Female" => $data->family_planning_attendance_female, "Total" => $data->family_planning_attendance_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                }  
        }


        return view('reports.general-outpatient', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'general_outpatient' => $general_outpatient,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function LaboratoryInvestigations(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $laboratory_investigations = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['laboratory_investigations'] != '')
            $laboratory_investigations =$postData['laboratory_investigations']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $laboratory_investigations != ''){
                   $data = LaboratoryInvestigations::select(DB::raw('SUM(hematology_male) as hematology_male,
                                                                SUM(hematology_female) as hematology_female,
                                                                SUM(hematology_total) as hematology_total,

                                                                SUM(parasitology_male) as parasitology_male,
                                                                SUM(parasitology_female) as parasitology_female,
                                                                SUM(parasitology_total) as parasitology_total,

                                                                SUM(chemistry_male) as chemistry_male,
                                                                SUM(chemistry_female) as chemistry_female,
                                                                SUM(chemistry_total) as chemistry_total,

                                                                SUM(microbiology_male) as microbiology_male,
                                                                SUM(microbiology_female) as microbiology_female,
                                                                SUM(microbiology_total) as microbiology_total,

                                                                SUM(histology_male) as histology_male,
                                                                SUM(histology_female) as histology_female,
                                                                SUM(histology_total) as histology_total,

                                                                SUM(cyto_male) as cyto_male,
                                                                SUM(cyto_female) as cyto_female,
                                                                SUM(cyto_total) as cyto_total,

                                                                SUM(blood_transfusion_male) as blood_transfusion_male,
                                                                SUM(blood_transfusion_female) as blood_transfusion_female,
                                                                SUM(blood_transfusion_total) as blood_transfusion_total,

                                                                SUM(blood_donation_male) as blood_donation_male,
                                                                SUM(blood_donation_female) as blood_donation_female,
                                                                SUM(blood_donation_total) as blood_donation_total,added_by
                                                          '))
                                                          ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                          ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                        $data = LaboratoryInvestigations::select(DB::raw('SUM(hematology_male) as hematology_male,
                                                                SUM(hematology_female) as hematology_female,
                                                                SUM(hematology_total) as hematology_total,

                                                                SUM(parasitology_male) as parasitology_male,
                                                                SUM(parasitology_female) as parasitology_female,
                                                                SUM(parasitology_total) as parasitology_total,

                                                                SUM(chemistry_male) as chemistry_male,
                                                                SUM(chemistry_female) as chemistry_female,
                                                                SUM(chemistry_total) as chemistry_total,

                                                                SUM(microbiology_male) as microbiology_male,
                                                                SUM(microbiology_female) as microbiology_female,
                                                                SUM(microbiology_total) as microbiology_total,

                                                                SUM(histology_male) as histology_male,
                                                                SUM(histology_female) as histology_female,
                                                                SUM(histology_total) as histology_total,

                                                                SUM(cyto_male) as cyto_male,
                                                                SUM(cyto_female) as cyto_female,
                                                                SUM(cyto_total) as cyto_total,

                                                                SUM(blood_transfusion_male) as blood_transfusion_male,
                                                                SUM(blood_transfusion_female) as blood_transfusion_female,
                                                                SUM(blood_transfusion_total) as blood_transfusion_total,

                                                                SUM(blood_donation_male) as blood_donation_male,
                                                                SUM(blood_donation_female) as blood_donation_female,
                                                                SUM(blood_donation_total) as blood_donation_total
                                                          '))
                                                          ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                          ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                                ->first();
                }
            
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $laboratory_investigations != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($laboratory_investigations == 'hematology' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->hematology_male, "Female" => $value->hematology_female, "Total" => $value->hematology_total);
                        }
                        if($laboratory_investigations == 'parasitology' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->parasitology_male, "Female" => $value->parasitology_female, "Total" => $value->parasitology_total);
                        }
                        if($laboratory_investigations == 'chemistry' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->chemistry_male, "Female" =>$value->chemistry_female, "Total" => $value->chemistry_total);
                        }
                        if($laboratory_investigations == 'microbiology' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->microbiology_male, "Female" =>$value->microbiology_female, "Total" => $value->microbiology_total);
                        }
                        if($laboratory_investigations == 'histology' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->histology_male, "Female" => $value->histology_female, "Total" => $value->histology_total);
                        }
                        if($laboratory_investigations == 'cyto' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->cyto_male, "Female" => $value->cyto_female, "Total" => $value->cyto_total);
                        }
                        if($laboratory_investigations == 'blood_transfusion' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->blood_transfusion_male, "Female" => $value->blood_transfusion_female, "Total" => $value->blood_transfusion_total);
                        }
                        if($laboratory_investigations == 'blood_donation' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->blood_donation_male, "Female" => $value->blood_donation_female, "Total" => $value->blood_donation_total);
                        } 
                    }
                }else{
                    $chartData = array();
                    if($laboratory_investigations == '' || $laboratory_investigations == 'hematology' ){
                        $chartData['Hematology'] = array("Male" => $data->hematology_male, "Female" => $data->hematology_female, "Total" => $data->hematology_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'parasitology' ){
                        $chartData['Parasitology'] = array("Male" => $data->parasitology_male, "Female" => $data->parasitology_female, "Total" => $data->parasitology_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'chemistry' ){
                        $chartData['Chemistry'] = array("Male" => $data->chemistry_male, "Female" =>$data->chemistry_female, "Total" => $data->chemistry_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'microbiology' ){
                        $chartData['Microbiology'] = array("Male" => $data->microbiology_male, "Female" =>$data->microbiology_female, "Total" => $data->microbiology_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'histology' ){
                        $chartData['Histology'] = array("Male" => $data->histology_male, "Female" => $data->histology_female, "Total" => $data->histology_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'cyto' ){
                        $chartData['Cytology'] = array("Male" => $data->cyto_male, "Female" => $data->cyto_female, "Total" => $data->cyto_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'blood_transfusion' ){
                        $chartData['Blood Transfusion'] = array("Male" => $data->blood_transfusion_male, "Female" => $data->blood_transfusion_female, "Total" => $data->blood_transfusion_total);
                    }
                    if($laboratory_investigations == '' || $laboratory_investigations == 'blood_donation' ){
                        $chartData['Blood Donation'] = array("Male" => $data->blood_donation_male, "Female" => $data->blood_donation_female, "Total" => $data->blood_donation_total);
                    }     
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                } 
        }   

        return view('reports.laboratory-investigations', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'laboratory_investigations' => $laboratory_investigations,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function Operations(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['operations'] != '')
            $operations =$postData['operations']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $operations != ''){
                    $data = Operations::select(DB::raw('SUM(major_operation_male) as major_operation_male,
                                                            SUM(major_operation_female) as major_operation_female,
                                                            SUM(major_operation_total) as major_operation_total,

                                                            SUM(intermediate_operation_male) as intermediate_operation_male,
                                                            SUM(intermediate_operation_female) as intermediate_operation_female,
                                                            SUM(intermediate_operation_total) as intermediate_operation_total,

                                                            SUM(minor_operation_male) as minor_operation_male,
                                                            SUM(minor_operation_female) as minor_operation_female,
                                                            SUM(minor_operation_total) as minor_operation_total,

                                                            SUM(circumcision_male) as circumcision_male,
                                                            SUM(circumcision_female) as circumcision_female,
                                                            SUM(circumcision_total) as circumcision_total,added_by


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                    $data = Operations::select(DB::raw('SUM(major_operation_male) as major_operation_male,
                                                            SUM(major_operation_female) as major_operation_female,
                                                            SUM(major_operation_total) as major_operation_total,

                                                            SUM(intermediate_operation_male) as intermediate_operation_male,
                                                            SUM(intermediate_operation_female) as intermediate_operation_female,
                                                            SUM(intermediate_operation_total) as intermediate_operation_total,

                                                            SUM(minor_operation_male) as minor_operation_male,
                                                            SUM(minor_operation_female) as minor_operation_female,
                                                            SUM(minor_operation_total) as minor_operation_total,

                                                            SUM(circumcision_male) as circumcision_male,
                                                            SUM(circumcision_female) as circumcision_female,
                                                            SUM(circumcision_total) as circumcision_total


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $operations != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($operations == '' || $operations == 'major_operation' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->major_operation_male, "Female" => $value->major_operation_female, "Total" => $value->major_operation_ftotal);
                        }
                        if($operations == '' || $operations == 'intermediate_operation' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->intermediate_operation_male, "Female" =>$value->intermediate_operation_female, "Total" => $value->intermediate_operation_total);
                        }
                        if($operations == '' || $operations == 'minor_operation' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->minor_operation_male, "Female" =>$value->minor_operation_female, "Total" => $value->minor_operation_total);
                        }
                        if($operations == '' || $operations == 'circumcision' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->circumcision_male, "Female" => $value->circumcision_female, "Total" => $value->circumcision_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($operations == '' || $operations == 'major_operation' ){
                        $chartData['Major Operation'] = array("Male" => $data->major_operation_male, "Female" => $data->major_operation_female, "Total" => $data->major_operation_total);
                    }
                    if($operations == '' || $operations == 'intermediate_operation' ){
                        $chartData['Intermediate Operation'] = array("Male" => $data->intermediate_operation_male, "Female" =>$data->intermediate_operation_female, "Total" => $data->intermediate_operation_total);
                    }
                    if($operations == '' || $operations == 'minor_operation' ){
                        $chartData['Minor Operation'] = array("Male" => $data->minor_operation_male, "Female" =>$data->minor_operation_female, "Total" => $data->minor_operation_total);
                    }
                    if($operations == '' || $operations == 'circumcision' ){
                        $chartData['Circumcision'] = array("Male" => $data->circumcision_male, "Female" => $data->circumcision_female, "Total" => $data->circumcision_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                }
        }

        return view('reports.operations', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'operations' => $operations,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function SpecialConsultiveClinics(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['operations'] != '')
            $operations =$postData['operations']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $operations != ''){
                    $data = SpecialConsultiveClinics::select(DB::raw('SUM(obstetrics_gynecology_clinic_male) as obstetrics_gynecology_clinic_male,
                                                            SUM(obstetrics_gynecology_clinic_female) as obstetrics_gynecology_clinic_female,
                                                            SUM(obstetrics_gynecology_clinic_total) as obstetrics_gynecology_clinic_total,

                                                            SUM(surgical_clinic_male) as surgical_clinic_male,
                                                            SUM(surgical_clinic_female) as surgical_clinic_female,
                                                            SUM(surgical_clinic_total) as surgical_clinic_total,

                                                            SUM(medical_clinic_male) as medical_clinic_male,
                                                            SUM(medical_clinic_female) as medical_clinic_female,
                                                            SUM(medical_clinic_total) as medical_clinic_total,

                                                            SUM(ear_nose_throat_clinic_male) as ear_nose_throat_clinic_male,
                                                            SUM(ear_nose_throat_clinic_female) as ear_nose_throat_clinic_female,
                                                            SUM(ear_nose_throat_clinic_total) as ear_nose_throat_clinic_total,

                                                            SUM(dental_clinic_male) as dental_clinic_male,
                                                            SUM(dental_clinic_female) as dental_clinic_female,
                                                            SUM(dental_clinic_total) as dental_clinic_total,

                                                            SUM(ophthalmology_clinic_male) as ophthalmology_clinic_male,
                                                            SUM(ophthalmology_clinic_female) as ophthalmology_clinic_female,
                                                            SUM(ophthalmology_clinic_total) as ophthalmology_clinic_total,

                                                            SUM(optometric_clinic_male) as optometric_clinic_male,
                                                            SUM(optometric_clinic_female) as optometric_clinic_female,
                                                            SUM(optometric_clinic_total) as optometric_clinic_total,

                                                            SUM(urology_clinic_male) as urology_clinic_male,
                                                            SUM(urology_clinic_female) as urology_clinic_female,
                                                            SUM(urology_clinic_total) as urology_clinic_total,

                                                            SUM(orthopedics_clinic_male) as orthopedics_clinic_male,
                                                            SUM(orthopedics_clinic_female) as orthopedics_clinic_female,
                                                            SUM(orthopedics_clinic_total) as orthopedics_clinic_total,

                                                            SUM(pediatrics_clinic_male) as pediatrics_clinic_male,
                                                            SUM(pediatrics_clinic_female) as pediatrics_clinic_female,
                                                            SUM(pediatrics_clinic_total) as pediatrics_clinic_total,

                                                            SUM(physiotherapy_clinic_male) as physiotherapy_clinic_male,
                                                            SUM(physiotherapy_clinic_female) as physiotherapy_clinic_female,
                                                            SUM(physiotherapy_clinic_total) as physiotherapy_clinic_total,

                                                            SUM(comprehensive_site_male) as comprehensive_site_male,
                                                            SUM(comprehensive_site_female) as comprehensive_site_female,
                                                            SUM(comprehensive_site_total) as comprehensive_site_total,

                                                            SUM(neurology_clinic_male) as neurology_clinic_male,
                                                            SUM(neurology_clinic_female) as neurology_clinic_female,
                                                            SUM(neurology_clinic_total) as neurology_clinic_total,

                                                            SUM(nutrition_clinic_male) as nutrition_clinic_male,
                                                            SUM(nutrition_clinic_female) as nutrition_clinic_female,
                                                            SUM(nutrition_clinic_total) as nutrition_clinic_total,

                                                            SUM(dot_clinic_male) as dot_clinic_male,
                                                            SUM(dot_clinic_female) as dot_clinic_female,
                                                            SUM(dot_clinic_total) as dot_clinic_total,added_by,

                                                            SUM(peadiatrics_surgery_male) as peadiatrics_surgery_male,
                                                            SUM(peadiatrics_surgery_female) as peadiatrics_surgery_female,
                                                            SUM(peadiatrics_surgery_total) as peadiatrics_surgery_total,
   
                                                            SUM(dialysis_male) as dialysis_male,
                                                            SUM(dialysis_female) as dialysis_female,
                                                            SUM(dialysis_total) as dialysis_total,
   
                                                            SUM(total_dialysis_male) as total_dialysis_male,
                                                            SUM(total_dialysis_female) as total_dialysis_female,
                                                            SUM(total_dialysis_total) as total_dialysis_total,
   
                                                            SUM(dermatology_male) as dermatology_male,
                                                            SUM(dermatology_female) as dermatology_female,
                                                            SUM(dermatology_total) as dermatology_total,
   
                                                            SUM(pyschiatric_male) as pyschiatric_male,
                                                            SUM(pyschiatric_female) as pyschiatric_female,
                                                            SUM(pyschiatric_total) as pyschiatric_total,
   
                                                            SUM(plastic_male) as plastic_male,
                                                            SUM(plastic_female) as plastic_female,
                                                            SUM(plastic_total) as plastic_total


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                    $data = SpecialConsultiveClinics::select(DB::raw('SUM(obstetrics_gynecology_clinic_male) as obstetrics_gynecology_clinic_male,
                                                            SUM(obstetrics_gynecology_clinic_female) as obstetrics_gynecology_clinic_female,
                                                            SUM(obstetrics_gynecology_clinic_total) as obstetrics_gynecology_clinic_total,

                                                            SUM(surgical_clinic_male) as surgical_clinic_male,
                                                            SUM(surgical_clinic_female) as surgical_clinic_female,
                                                            SUM(surgical_clinic_total) as surgical_clinic_total,

                                                            SUM(medical_clinic_male) as medical_clinic_male,
                                                            SUM(medical_clinic_female) as medical_clinic_female,
                                                            SUM(medical_clinic_total) as medical_clinic_total,

                                                            SUM(ear_nose_throat_clinic_male) as ear_nose_throat_clinic_male,
                                                            SUM(ear_nose_throat_clinic_female) as ear_nose_throat_clinic_female,
                                                            SUM(ear_nose_throat_clinic_total) as ear_nose_throat_clinic_total,

                                                            SUM(dental_clinic_male) as dental_clinic_male,
                                                            SUM(dental_clinic_female) as dental_clinic_female,
                                                            SUM(dental_clinic_total) as dental_clinic_total,

                                                            SUM(ophthalmology_clinic_male) as ophthalmology_clinic_male,
                                                            SUM(ophthalmology_clinic_female) as ophthalmology_clinic_female,
                                                            SUM(ophthalmology_clinic_total) as ophthalmology_clinic_total,

                                                            SUM(optometric_clinic_male) as optometric_clinic_male,
                                                            SUM(optometric_clinic_female) as optometric_clinic_female,
                                                            SUM(optometric_clinic_total) as optometric_clinic_total,

                                                            SUM(urology_clinic_male) as urology_clinic_male,
                                                            SUM(urology_clinic_female) as urology_clinic_female,
                                                            SUM(urology_clinic_total) as urology_clinic_total,

                                                            SUM(orthopedics_clinic_male) as orthopedics_clinic_male,
                                                            SUM(orthopedics_clinic_female) as orthopedics_clinic_female,
                                                            SUM(orthopedics_clinic_total) as orthopedics_clinic_total,

                                                            SUM(pediatrics_clinic_male) as pediatrics_clinic_male,
                                                            SUM(pediatrics_clinic_female) as pediatrics_clinic_female,
                                                            SUM(pediatrics_clinic_total) as pediatrics_clinic_total,

                                                            SUM(physiotherapy_clinic_male) as physiotherapy_clinic_male,
                                                            SUM(physiotherapy_clinic_female) as physiotherapy_clinic_female,
                                                            SUM(physiotherapy_clinic_total) as physiotherapy_clinic_total,

                                                            SUM(comprehensive_site_male) as comprehensive_site_male,
                                                            SUM(comprehensive_site_female) as comprehensive_site_female,
                                                            SUM(comprehensive_site_total) as comprehensive_site_total,

                                                            SUM(neurology_clinic_male) as neurology_clinic_male,
                                                            SUM(neurology_clinic_female) as neurology_clinic_female,
                                                            SUM(neurology_clinic_total) as neurology_clinic_total,

                                                            SUM(nutrition_clinic_male) as nutrition_clinic_male,
                                                            SUM(nutrition_clinic_female) as nutrition_clinic_female,
                                                            SUM(nutrition_clinic_total) as nutrition_clinic_total,

                                                            SUM(dot_clinic_male) as dot_clinic_male,
                                                            SUM(dot_clinic_female) as dot_clinic_female,
                                                            SUM(dot_clinic_total) as dot_clinic_total,

                                                            SUM(peadiatrics_surgery_male) as peadiatrics_surgery_male,
                                                            SUM(peadiatrics_surgery_female) as peadiatrics_surgery_female,
                                                            SUM(peadiatrics_surgery_total) as peadiatrics_surgery_total,
   
                                                            SUM(dialysis_male) as dialysis_male,
                                                            SUM(dialysis_female) as dialysis_female,
                                                            SUM(dialysis_total) as dialysis_total,
   
                                                            SUM(total_dialysis_male) as total_dialysis_male,
                                                            SUM(total_dialysis_female) as total_dialysis_female,
                                                            SUM(total_dialysis_total) as total_dialysis_total,
   
                                                            SUM(dermatology_male) as dermatology_male,
                                                            SUM(dermatology_female) as dermatology_female,
                                                            SUM(dermatology_total) as dermatology_total,
   
                                                            SUM(pyschiatric_male) as pyschiatric_male,
                                                            SUM(pyschiatric_female) as pyschiatric_female,
                                                            SUM(pyschiatric_total) as pyschiatric_total,
   
                                                            SUM(plastic_male) as plastic_male,
                                                            SUM(plastic_female) as plastic_female,
                                                            SUM(plastic_total) as plastic_total


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
        }

        // echo "<pre>"; print_r($data); exit; 
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
            if($hospitalname1 == "" && $operations != "")
            {
                $chartData = array();
                foreach($data as $key => $value){
                    if($operations == 'obstetrics_gynecology_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->obstetrics_gynecology_clinic_male, "Female" => $value->obstetrics_gynecology_clinic_female, "Total" => $value->obstetrics_gynecology_clinic_total);
                    }
                    if($operations == 'surgical_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->surgical_clinic_male, "Female" => $value->surgical_clinic_female, "Total" => $value->surgical_clinic_total);
                    }
                    if($operations == 'medical_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->medical_clinic_male, "Female" =>$value->medical_clinic_female, "Total" => $value->medical_clinic_total);
                    }
                    if($operations == 'ear_nose_throat_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->ear_nose_throat_clinic_male, "Female" =>$value->ear_nose_throat_clinic_female, "Total" => $value->ear_nose_throat_clinic_total);
                    }
                    if($operations == 'dental_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->dental_clinic_male, "Female" => $value->dental_clinic_female, "Total" => $value->dental_clinic_total);
                    }
                    if($operations == 'ophthalmology_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->ophthalmology_clinic_male, "Female" => $value->ophthalmology_clinic_female, "Total" => $value->ophthalmology_clinic_total);
                    }
                    if($operations == 'optometric_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->optometric_clinic_male, "Female" => $value->optometric_clinic_female, "Total" => $value->optometric_clinic_total);
                    }
                    if($operations == 'urology_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->urology_clinic_male, "Female" =>$value->urology_clinic_female, "Total" => $value->urology_clinic_total);
                    }
                    if($operations == 'orthopedics_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->orthopedics_clinic_male, "Female" =>$value->orthopedics_clinic_female, "Total" => $value->orthopedics_clinic_total);
                    }
                    if($operations == 'pediatrics_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->pediatrics_clinic_male, "Female" => $value->pediatrics_clinic_female, "Total" => $value->pediatrics_clinic_total);
                    }
                    if($operations == 'physiotherapy_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->physiotherapy_clinic_male, "Female" => $value->physiotherapy_clinic_female, "Total" => $value->physiotherapy_clinic_total);
                    }
                    if($operations == 'comprehensive_site' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->comprehensive_site_male, "Female" => $value->comprehensive_site_female, "Total" => $value->comprehensive_site_total);
                    }
                    if($operations == 'neurology_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->neurology_clinic_male, "Female" =>$value->neurology_clinic_female, "Total" => $value->neurology_clinic_total);
                    }
                    if($operations == 'nutrition_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->nutrition_clinic_male, "Female" =>$value->nutrition_clinic_female, "Total" => $value->nutrition_clinic_total);
                    }
                    if($operations == 'dot_clinic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->dot_clinic_male, "Female" => $value->dot_clinic_female, "Total" => $value->dot_clinic_total);
                    }
                    if($operations == 'paediatrics_surgery' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->paediatrics_surgery_male, "Female" => $value->paediatrics_surgery_female, "Total" => $value->paediatrics_surgery_total);
                    }
                    if($operations == 'dialysis' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->dialysis_male, "Female" => $value->dialysis_female, "Total" => $value->dialysis_total);
                    }
                    if($operations == 'total_dialysis' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->total_dialysis_male, "Female" => $value->total_dialysis_female, "Total" => $value->total_dialysis_total);
                    }
                    if($operations == 'dermatology' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->dermatology_male, "Female" => $value->dermatology_female, "Total" => $value->dermatology_total);
                    }
                    if($operations == 'pyschiatric' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->pyschiatric_male, "Female" => $value->pyschiatric_female, "Total" => $value->pyschiatric_total);
                    }
                    if($operations == 'plastic' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->plastic_male, "Female" => $value->plastic_female, "Total" => $value->plastic_total);
                    }
                }
            }else{
                $chartData = array();
                if($operations == '' || $operations == 'obstetrics_gynecology_clinic' ){
                    $chartData['Obstetrics Gynecology Clinic'] = array("Male" => $data->obstetrics_gynecology_clinic_male, "Female" => $data->obstetrics_gynecology_clinic_female, "Total" => $data->obstetrics_gynecology_clinic_total);
                }
                if($operations == '' || $operations == 'surgical_clinic' ){
                    $chartData['Surgical Clinic'] = array("Male" => $data->surgical_clinic_male, "Female" => $data->surgical_clinic_female, "Total" => $data->surgical_clinic_total);
                }
                if($operations == '' || $operations == 'medical_clinic' ){
                    $chartData['Medical Clinic'] = array("Male" => $data->medical_clinic_male, "Female" =>$data->medical_clinic_female, "Total" => $data->medical_clinic_total);
                }
                if($operations == '' || $operations == 'ear_nose_throat_clinic' ){
                    $chartData['Ear Nose Throat Clinic'] = array("Male" => $data->ear_nose_throat_clinic_male, "Female" =>$data->ear_nose_throat_clinic_female, "Total" => $data->ear_nose_throat_clinic_total);
                }
                if($operations == '' || $operations == 'dental_clinic' ){
                    $chartData['Dental Clinic'] = array("Male" => $data->dental_clinic_male, "Female" => $data->dental_clinic_female, "Total" => $data->dental_clinic_total);
                }
                if($operations == '' || $operations == 'ophthalmology_clinic' ){
                    $chartData['Ophthalmology Clinic'] = array("Male" => $data->ophthalmology_clinic_male, "Female" => $data->ophthalmology_clinic_female, "Total" => $data->ophthalmology_clinic_total);
                }
                if($operations == '' || $operations == 'optometric_clinic' ){
                    $chartData['Optometric Clinic'] = array("Male" => $data->optometric_clinic_male, "Female" => $data->optometric_clinic_female, "Total" => $data->optometric_clinic_total);
                }
                if($operations == '' || $operations == 'urology_clinic' ){
                    $chartData['Urology Clinic'] = array("Male" => $data->urology_clinic_male, "Female" =>$data->urology_clinic_female, "Total" => $data->urology_clinic_total);
                }
                if($operations == '' || $operations == 'orthopedics_clinic' ){
                    $chartData['Orthopedics Clinic'] = array("Male" => $data->orthopedics_clinic_male, "Female" =>$data->orthopedics_clinic_female, "Total" => $data->orthopedics_clinic_total);
                }
                if($operations == '' || $operations == 'pediatrics_clinic' ){
                    $chartData['Paediatrics Clinic'] = array("Male" => $data->pediatrics_clinic_male, "Female" => $data->pediatrics_clinic_female, "Total" => $data->pediatrics_clinic_total);
                }
                if($operations == '' || $operations == 'physiotherapy_clinic' ){
                    $chartData['Physiotherapy Clinic'] = array("Male" => $data->physiotherapy_clinic_male, "Female" => $data->physiotherapy_clinic_female, "Total" => $data->physiotherapy_clinic_total);
                }
                if($operations == '' || $operations == 'comprehensive_site' ){
                    $chartData['Comprehensive Site(HIV/AIDS)'] = array("Male" => $data->comprehensive_site_male, "Female" => $data->comprehensive_site_female, "Total" => $data->comprehensive_site_total);
                }
                if($operations == '' || $operations == 'neurology_clinic' ){
                    $chartData['Neurology Clinic'] = array("Male" => $data->neurology_clinic_male, "Female" =>$data->neurology_clinic_female, "Total" => $data->neurology_clinic_total);
                }
                if($operations == '' || $operations == 'nutrition_clinic' ){
                    $chartData['Nutrition Clinic'] = array("Male" => $data->nutrition_clinic_male, "Female" =>$data->nutrition_clinic_female, "Total" => $data->nutrition_clinic_total);
                }
                if($operations == '' || $operations == 'dot_clinic' ){
                    $chartData['DOT Clinic'] = array("Male" => $data->dot_clinic_male, "Female" => $data->dot_clinic_female, "Total" => $data->dot_clinic_total);
                }
                if($operations == '' || $operations == 'paediatrics_surgery' ){
                    $chartData['Paediatrics Surgery'] = array("Male" => $data->paediatrics_surgery_male, "Female" => $data->paediatrics_surgery_female, "Total" => $data->paediatrics_surgery_total);
                }
                if($operations == '' || $operations == 'dialysis' ){
                    $chartData['Dialysis (New)'] = array("Male" => $data->dialysis_male, "Female" => $data->dialysis_female, "Total" => $data->dialysis_total);
                }
                if($operations == '' || $operations == 'total_dialysis' ){
                    $chartData['Total Section of Dialysis'] = array("Male" => $data->total_dialysis_male, "Female" => $data->total_dialysis_female, "Total" => $data->total_dialysis_total);
                }
                if($operations == '' || $operations == 'dermatology' ){
                    $chartData['Dermatology Clinic'] = array("Male" => $data->dermatology_male, "Female" => $data->dermatology_female, "Total" => $data->dermatology_total);
                }
                if($operations == '' || $operations == 'pyschiatric' ){
                    $chartData['Pyschiatric Clinic'] = array("Male" => $data->pyschiatric_male, "Female" => $data->pyschiatric_female, "Total" => $data->pyschiatric_total);
                }
                if($operations == '' || $operations == 'plastic' ){
                    $chartData['Plastic Clinic'] = array("Male" => $data->plastic_male, "Female" => $data->plastic_female, "Total" => $data->plastic_total);
                } 
            }
                

            $labelArray = array_keys($chartData);
            $chartFinalData = array();
            $piechartData = array();
            $pieChartColor  = array();
            foreach($chartData as $key => $chart){
                $k = 0;
                foreach($chart as $index => $value){
                    if($index == "Total"){
                        $piechartData[$k][$key." ".$index] = $value;
                        $pieChartColor[] = $this->rndRGBColorCode();
                    }
                    if($index != "Total"){    
                        if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                            $chartFinalData[$k]['data'][] = $value;
                        }else{
                            $chartFinalData[$k]['label'] = $index;
                            $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['data'][] = $value;
                            }
                        }
                    }
                    $k++;   
                }
            }
            if(!empty($piechartData)){
                $piechartData = array_values($piechartData);
                $piechartData = $piechartData[0]; 
            }else{
                $piechartData = array();
            } 
        }

        return view('reports.special-consultive-clinics', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'operations' => $operations,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function RadioDiagnosis(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $radio_diagnosis = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['radio_diagnosis'] != '')
            $radio_diagnosis =$postData['radio_diagnosis']; 
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $radio_diagnosis != ''){
                    $data = RadioDiagnosis::select(DB::raw('SUM(contrast_x_ray_inpatients_male) as contrast_x_ray_inpatients_male,
                                                            SUM(contrast_x_ray_inpatients_female) as contrast_x_ray_inpatients_female,
                                                            SUM(contrast_x_ray_inpatients_total) as contrast_x_ray_inpatients_total,

                                                            SUM(contrast_x_ray_outpatients_male) as contrast_x_ray_outpatients_male,
                                                            SUM(contrast_x_ray_outpatients_female) as contrast_x_ray_outpatients_female,
                                                            SUM(contrast_x_ray_outpatients_total) as contrast_x_ray_outpatients_total,

                                                            SUM(ultrasound_inpatients_male) as ultrasound_inpatients_male,
                                                            SUM(ultrasound_inpatients_female) as ultrasound_inpatients_female,
                                                            SUM(ultrasound_inpatients_total) as ultrasound_inpatients_total,

                                                            SUM(ultrasound_outpatients_male) as ultrasound_outpatients_male,
                                                            SUM(ultrasound_outpatients_female) as ultrasound_outpatients_female,
                                                            SUM(ultrasound_outpatients_total) as ultrasound_outpatients_total,

                                                            SUM(ecg_male) as ecg_male,
                                                            SUM(ecg_female) as ecg_female,
                                                            SUM(ecg_total) as ecg_total,

                                                            SUM(echo_male) as echo_male,
                                                            SUM(echo_female) as echo_female,
                                                            SUM(echo_total) as echo_total,

                                                            SUM(mamogramph_male) as mamogramph_male,
                                                            SUM(mamogramph_female) as mamogramph_female,
                                                            SUM(mamogramph_total) as mamogramph_total,

                                                            SUM(hsg_male) as hsg_male,
                                                            SUM(hsg_female) as hsg_female,
                                                            SUM(hsg_total) as hsg_total,

                                                            SUM(ct_scan_male) as ct_scan_male,
                                                            SUM(ct_scan_female) as ct_scan_female,
                                                            SUM(ct_scan_total) as ct_scan_total,added_by


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                   
               }else{
                   $data = RadioDiagnosis::select(DB::raw('SUM(contrast_x_ray_inpatients_male) as contrast_x_ray_inpatients_male,
                                                            SUM(contrast_x_ray_inpatients_female) as contrast_x_ray_inpatients_female,
                                                            SUM(contrast_x_ray_inpatients_total) as contrast_x_ray_inpatients_total,

                                                            SUM(contrast_x_ray_outpatients_male) as contrast_x_ray_outpatients_male,
                                                            SUM(contrast_x_ray_outpatients_female) as contrast_x_ray_outpatients_female,
                                                            SUM(contrast_x_ray_outpatients_total) as contrast_x_ray_outpatients_total,

                                                            SUM(ultrasound_inpatients_male) as ultrasound_inpatients_male,
                                                            SUM(ultrasound_inpatients_female) as ultrasound_inpatients_female,
                                                            SUM(ultrasound_inpatients_total) as ultrasound_inpatients_total,

                                                            SUM(ultrasound_outpatients_male) as ultrasound_outpatients_male,
                                                            SUM(ultrasound_outpatients_female) as ultrasound_outpatients_female,
                                                            SUM(ultrasound_outpatients_total) as ultrasound_outpatients_total,

                                                            SUM(ecg_male) as ecg_male,
                                                            SUM(ecg_female) as ecg_female,
                                                            SUM(ecg_total) as ecg_total,

                                                            SUM(echo_male) as echo_male,
                                                            SUM(echo_female) as echo_female,
                                                            SUM(echo_total) as echo_total,

                                                            SUM(mamogramph_male) as mamogramph_male,
                                                            SUM(mamogramph_female) as mamogramph_female,
                                                            SUM(mamogramph_total) as mamogramph_total,

                                                            SUM(hsg_male) as hsg_male,
                                                            SUM(hsg_female) as hsg_female,
                                                            SUM(hsg_total) as hsg_total,

                                                            SUM(ct_scan_male) as ct_scan_male,
                                                            SUM(ct_scan_female) as ct_scan_female,
                                                            SUM(ct_scan_total) as ct_scan_total


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
        }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
            if($hospitalname1 == '' && $radio_diagnosis != ''){
                $chartData = array();
                foreach($data as $key => $value){
                    if($radio_diagnosis == 'contrast_x_ray' ){
                        $chartData[$value->hospital_name.' Inpatients'] = array("Male" => $value->contrast_x_ray_inpatients_male, "Female" => $value->contrast_x_ray_inpatients_female, "Total" => $value->contrast_x_ray_inpatients_total);
                    }
                    if($radio_diagnosis == 'contrast_x_ray' ){
                        $chartData[$value->hospital_name.' Outpatients'] = array("Male" => $value->contrast_x_ray_outpatients_male, "Female" => $value->contrast_x_ray_outpatients_female, "Total" => $value->contrast_x_ray_outpatients_total);
                    }
                    if($radio_diagnosis == 'ultrasound' ){
                        $chartData[$value->hospital_name.' Inpatients'] = array("Male" => $value->ultrasound_inpatients_male, "Female" => $value->ultrasound_inpatients_female, "Total" => $value->ultrasound_inpatients_total);
                    }
                    if($radio_diagnosis == 'ultrasound' ){
                        $chartData[$value->hospital_name.' Outpatients'] = array("Male" => $value->ultrasound_outpatients_male, "Female" => $value->ultrasound_outpatients_female, "Total" => $value->ultrasound_outpatients_total);
                    }
                    if($radio_diagnosis == 'ecg' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->ecg_male, "Female" => $value->ecg_female, "Total" => $value->ecg_total);
                    }
                    if($radio_diagnosis == 'echo' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->echo_male, "Female" => $value->echo_female, "Total" => $value->echo_total);
                    }
                    if($radio_diagnosis == 'mamogramph' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->mamogramph_male, "Female" => $value->mamogramph_female, "Total" => $value->mamogramph_total);
                    }
                    if($radio_diagnosis == 'ct_scan' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->ct_scan_male, "Female" =>$value->ct_scan_female, "Total" =>$value->ct_scan_total);
                    }
                    if($radio_diagnosis == 'hsg' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->hsg_male, "Female" =>$value->hsg_female, "Total" =>$value->hsg_total);
                    }
                }
            }else{
                $chartData = array();
                if($radio_diagnosis == '' || $radio_diagnosis == 'contrast_x_ray' ){
                    $chartData['Contrast X-Ray Inpatients'] = array("Male" => $data->contrast_x_ray_inpatients_male, "Female" => $data->contrast_x_ray_inpatients_female, "Total" => $data->contrast_x_ray_inpatients_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'contrast_x_ray' ){
                    $chartData['Contrast X-Ray Outpatients'] = array("Male" => $data->contrast_x_ray_outpatients_male, "Female" => $data->contrast_x_ray_outpatients_female, "Total" => $data->contrast_x_ray_outpatients_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'ultrasound' ){
                    $chartData['Ultrasound Inpatients'] = array("Male" => $data->ultrasound_inpatients_male, "Female" => $data->ultrasound_inpatients_female, "Total" => $data->ultrasound_inpatients_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'ultrasound' ){
                    $chartData['Ultrasound Outpatients'] = array("Male" => $data->ultrasound_outpatients_male, "Female" => $data->ultrasound_outpatients_female, "Total" => $data->ultrasound_outpatients_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'ecg' ){
                    $chartData['ECG'] = array("Male" => $data->ecg_male, "Female" => $data->ecg_female, "Total" => $data->ecg_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'echo' ){
                    $chartData['ECHO'] = array("Male" => $data->echo_male, "Female" => $data->echo_female, "Total" => $data->echo_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'mamogramph' ){
                    $chartData['Mamogramph'] = array("Male" => $data->mamogramph_male, "Female" => $data->mamogramph_female, "Total" => $data->mamogramph_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'ct_scan' ){
                    $chartData['CT Scan'] = array("Male" => $data->ct_scan_male, "Female" => $data->ct_scan_female, "Total" => $data->ct_scan_total);
                }
                if($radio_diagnosis == '' || $radio_diagnosis == 'hsg' ){
                    $chartData['HSG'] = array("Male" => $data->hsg_male, "Female" => $data->hsg_female, "Total" => $data->hsg_total);
                }
            }

            $labelArray = array_keys($chartData);
            $chartFinalData = array();
            $piechartData = array();
            $pieChartColor  = array();
            foreach($chartData as $key => $chart){
                $k = 0;
                foreach($chart as $index => $value){
                    if($index == "Total"){
                        $piechartData[$k][$key." ".$index] = $value;
                        $pieChartColor[] = $this->rndRGBColorCode();
                    }
                    if($index != "Total"){
                        if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                            $chartFinalData[$k]['data'][] = $value;
                        }else{
                            $chartFinalData[$k]['label'] = $index;
                            $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['data'][] = $value;
                            }
                        }
                    }
                    $k++;   
                }
            }
            // echo "<pre>";
            // print_r($piechartData);exit;
            
            if(!empty($piechartData)){
                $piechartData = array_values($piechartData);
                $piechartData = $piechartData[0]; 
            }else{
                $piechartData = array();
            }
            
        }

            // echo "<pre>";
            // print_r($chartFinalData);
            // exit;

        return view('reports.radio-diagnosis', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'radio_diagnosis' => $radio_diagnosis,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function MaternityReturns(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $radio_diagnosis = '';
        $radio_diagnosis1 = '';
        $radio_diagnosis2 = '';
        $radio_diagnosis3 = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['radio_diagnosis'] != '')
            $radio_diagnosis =$postData['radio_diagnosis']; 
            $radio_diagnosis2 = str_replace("_", " ",$radio_diagnosis);
        }
        if(isset($postData) && count($postData) > 0 ){
            if($postData['radio_diagnosis1'] != '')
            $radio_diagnosis1 =$postData['radio_diagnosis1']; 
            $radio_diagnosis3 = str_replace("_", " ",$radio_diagnosis1);
        }
        
        $data = array();

           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $radio_diagnosis != ''){
                    $data = MaternityReturns::select(DB::raw('SUM(live_birth_male) as live_birth_male,
                                                            SUM(live_birth_female) as live_birth_female,
                                                            SUM(live_birth_total) as live_birth_total,

                                                            SUM(fresh_still_birth_male) as fresh_still_birth_male,
                                                            SUM(fresh_still_birth_female) as fresh_still_birth_female,
                                                            SUM(fresh_still_birth_total) as fresh_still_birth_total,

                                                            SUM(macerated_still_birth_male) as macerated_still_birth_male,
                                                            SUM(macerated_still_birth_female) as macerated_still_birth_female,
                                                            SUM(macerated_still_birth_total) as macerated_still_birth_total,

                                                            SUM(asphyxia_male) as asphyxia_male,
                                                            SUM(asphyxia_female) as asphyxia_female,
                                                            SUM(asphyxia_total) as asphyxia_total,

                                                            SUM(low_birth_weight_male) as low_birth_weight_male,
                                                            SUM(low_birth_weight_female) as low_birth_weight_female,
                                                            SUM(low_birth_weight_total) as low_birth_weight_total,

                                                            SUM(macrosomic_babies_male) as macrosomic_babies_male,
                                                            SUM(macrosomic_babies_female) as macrosomic_babies_female,
                                                            SUM(macrosomic_babies_total) as macrosomic_babies_total,

                                                            SUM(immediate_neo_natal_death_male) as immediate_neo_natal_death_male,
                                                            SUM(immediate_neo_natal_death_female) as immediate_neo_natal_death_female,
                                                            SUM(immediate_neo_natal_death_total) as immediate_neo_natal_death_total,

                                                            SUM(born_before_arrival_male) as born_before_arrival_male,
                                                            SUM(born_before_arrival_female) as born_before_arrival_female,
                                                            SUM(born_before_arrival_total) as born_before_arrival_total,

                                                            SUM(pre_maturity_male) as pre_maturity_male,
                                                            SUM(pre_maturity_female) as pre_maturity_female,
                                                            SUM(pre_maturity_total) as pre_maturity_total,

                                                            SUM(booked_cases_total) as booked_cases_total,
                                                            SUM(unbooked_cases_total) as unbooked_cases_total,
                                                            SUM(svd_total) as svd_total,
                                                            
                                                            SUM(elective_c_s_total) as elective_c_s_total,
                                                            SUM(emergency_c_s_total) as emergency_c_s_total,
                                                            SUM(breech_delivery_total) as breech_delivery_total,
                                                            
                                                            SUM(twin_delivery_total) as twin_delivery_total,
                                                            SUM(vacum_delivery_total) as vacum_delivery_total,
                                                            SUM(forceps_delivery_total) as forceps_delivery_total,
                                                            
                                                            SUM(induction_of_labour_total) as induction_of_labour_total,
                                                            SUM(preterm_labour_total) as preterm_labour_total,
                                                            SUM(manual_removal_of_placenta_total) as manual_removal_of_placenta_total,
                                                            
                                                            SUM(pph_post_partum_total) as pph_post_partum_total,
                                                            SUM(prm_total) as prm_total,
                                                            SUM(aph_total) as aph_total,
                                                            
                                                            SUM(placenta_previa_total) as placenta_previa_total,
                                                            SUM(abruption_placenta_total) as abruption_placenta_total,
                                                            SUM(pre_eclampsia_total) as pre_eclampsia_total,
                                                            
                                                            SUM(eclampsia_total) as eclampsia_total,
                                                            SUM(iufd_total) as iufd_total,
                                                            SUM(maternal_death_total) as maternal_death_total,
                                                            SUM(mva) as mva,
                                                            SUM(total_no_deliveries) as total_no_deliveries,added_by


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                   $data = MaternityReturns::select(DB::raw('SUM(live_birth_male) as live_birth_male,
                                                            SUM(live_birth_female) as live_birth_female,
                                                            SUM(live_birth_total) as live_birth_total,

                                                            SUM(fresh_still_birth_male) as fresh_still_birth_male,
                                                            SUM(fresh_still_birth_female) as fresh_still_birth_female,
                                                            SUM(fresh_still_birth_total) as fresh_still_birth_total,

                                                            SUM(macerated_still_birth_male) as macerated_still_birth_male,
                                                            SUM(macerated_still_birth_female) as macerated_still_birth_female,
                                                            SUM(macerated_still_birth_total) as macerated_still_birth_total,

                                                            SUM(asphyxia_male) as asphyxia_male,
                                                            SUM(asphyxia_female) as asphyxia_female,
                                                            SUM(asphyxia_total) as asphyxia_total,

                                                            SUM(low_birth_weight_male) as low_birth_weight_male,
                                                            SUM(low_birth_weight_female) as low_birth_weight_female,
                                                            SUM(low_birth_weight_total) as low_birth_weight_total,

                                                            SUM(macrosomic_babies_male) as macrosomic_babies_male,
                                                            SUM(macrosomic_babies_female) as macrosomic_babies_female,
                                                            SUM(macrosomic_babies_total) as macrosomic_babies_total,

                                                            SUM(immediate_neo_natal_death_male) as immediate_neo_natal_death_male,
                                                            SUM(immediate_neo_natal_death_female) as immediate_neo_natal_death_female,
                                                            SUM(immediate_neo_natal_death_total) as immediate_neo_natal_death_total,

                                                            SUM(born_before_arrival_male) as born_before_arrival_male,
                                                            SUM(born_before_arrival_female) as born_before_arrival_female,
                                                            SUM(born_before_arrival_total) as born_before_arrival_total,

                                                            SUM(pre_maturity_male) as pre_maturity_male,
                                                            SUM(pre_maturity_female) as pre_maturity_female,
                                                            SUM(pre_maturity_total) as pre_maturity_total,

                                                            SUM(booked_cases_total) as booked_cases_total,
                                                            SUM(unbooked_cases_total) as unbooked_cases_total,
                                                            SUM(svd_total) as svd_total,
                                                            
                                                            SUM(elective_c_s_total) as elective_c_s_total,
                                                            SUM(emergency_c_s_total) as emergency_c_s_total,
                                                            SUM(breech_delivery_total) as breech_delivery_total,
                                                            
                                                            SUM(twin_delivery_total) as twin_delivery_total,
                                                            SUM(vacum_delivery_total) as vacum_delivery_total,
                                                            SUM(forceps_delivery_total) as forceps_delivery_total,
                                                            
                                                            SUM(induction_of_labour_total) as induction_of_labour_total,
                                                            SUM(preterm_labour_total) as preterm_labour_total,
                                                            SUM(manual_removal_of_placenta_total) as manual_removal_of_placenta_total,
                                                            
                                                            SUM(pph_post_partum_total) as pph_post_partum_total,
                                                            SUM(prm_total) as prm_total,
                                                            SUM(aph_total) as aph_total,
                                                            
                                                            SUM(placenta_previa_total) as placenta_previa_total,
                                                            SUM(abruption_placenta_total) as abruption_placenta_total,
                                                            SUM(pre_eclampsia_total) as pre_eclampsia_total,
                                                            
                                                            SUM(eclampsia_total) as eclampsia_total,
                                                            SUM(iufd_total) as iufd_total,
                                                            SUM(maternal_death_total) as maternal_death_total,
                                                            SUM(mva) as mva,
                                                            SUM(total_no_deliveries) as total_no_deliveries


                                                      '))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
        }

        $total_maternity_return = 0;
        // echo "<pre>"; print_r($data[0]); exit;
        if(!empty($data[0]) && isset($data[0])){
            foreach($data[0] as $datakey => $datavalue){
                $total_maternity_return = $total_maternity_return + $datavalue;
            }
        }else{
            foreach($data as $datakey => $datavalue){
                $total_maternity_return = $total_maternity_return + $datavalue;
            }
        }

        // echo $total_maternity_return; exit;
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
        return view('reports.maternity-returns', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'radio_diagnosis' => $radio_diagnosis,
            'radio_diagnosis1' => $radio_diagnosis1,
            'radio_diagnosis2' => $radio_diagnosis2,
            'radio_diagnosis3' => $radio_diagnosis3,
            'postData' => $postData]);
    }
   
    public function MonthlyHospitalStatistics1(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
                $postData['month_from'] = str_replace('/','-',$postData['month_from']);
                $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['operations'] != '')
            $operations =$postData['operations']; 
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $operations != ''){
                    $data = MonthlyHospitalStatistics::select(DB::raw('SUM(gopd_a_a_male) as gopd_a_a_male,
                                                            SUM(gopd_a_a_female) as gopd_a_a_female,
                                                            SUM(gopd_a_a_total) as gopd_a_a_total,

                                                            SUM(g_a_p_male) as g_a_p_male,
                                                            SUM(g_a_p_female) as g_a_p_female,
                                                            SUM(g_a_p_total) as g_a_p_total,

                                                            SUM(gopd_a_e_male) as gopd_a_e_male,
                                                            SUM(gopd_a_e_female) as gopd_a_e_female,
                                                            SUM(gopd_a_e_total) as gopd_a_e_total,

                                                            SUM(anc_male) as anc_male,
                                                            SUM(anc_female) as anc_female,
                                                            SUM(anc_total) as anc_total,

                                                            SUM(g_o_male) as g_o_male,
                                                            SUM(g_o_female) as g_o_female,
                                                            SUM(g_o_total) as g_o_total,

                                                            SUM(post_male) as post_male,
                                                            SUM(post_female) as post_female,
                                                            SUM(post_total) as post_total,

                                                            SUM(family_p_male) as family_p_male,
                                                            SUM(family_p_female) as family_p_female,
                                                            SUM(family_p_total) as family_p_total,

                                                            SUM(operation_male) as operation_male,
                                                            SUM(operation_female) as operation_female,
                                                            SUM(operation_total) as operation_total,

                                                            SUM(sopd_male) as sopd_male,
                                                            SUM(sopd_female) as sopd_female,
                                                            SUM(sopd_total) as sopd_total,

                                                            SUM(blood_d_male) as blood_d_male,
                                                            SUM(blood_d_female) as blood_d_female,
                                                            SUM(blood_d_total) as blood_d_total,

                                                            SUM(immu_male) as immu_male,
                                                            SUM(immu_female) as immu_female,
                                                            SUM(immu_total) as immu_total,

                                                            SUM(mopd_male) as mopd_male,
                                                            SUM(mopd_female) as mopd_female,
                                                            SUM(mopd_total) as mopd_total,

                                                            SUM(hiv_male) as hiv_male,
                                                            SUM(hiv_female) as hiv_female,
                                                            SUM(hiv_total) as hiv_total,

                                                            SUM(orthopedic_male) as orthopedic_male,
                                                            SUM(orthopedic_female) as orthopedic_female,
                                                            SUM(orthopedic_total) as orthopedic_total,

                                                            SUM(optometric_male) as optometric_male,
                                                            SUM(optometric_female) as optometric_female,
                                                            SUM(optometric_total) as optometric_total,
                                                            
                                                            SUM(neurology_male) as neurology_male,
                                                            SUM(neurology_female) as neurology_female,
                                                            SUM(neurology_total) as neurology_total,
                                                            
                                                            
                                                            SUM(urology_male) as urology_male,
                                                            SUM(urology_female) as urology_female,
                                                            SUM(urology_total) as urology_total,
                                                            
                                                            
                                                            SUM(physiotherapy_male) as physiotherapy_male,
                                                            SUM(physiotherapy_female) as physiotherapy_female,
                                                            SUM(physiotherapy_total) as physiotherapy_total,
                                                            
                                                            
                                                            SUM(nutrition_male) as nutrition_male,
                                                            SUM(nutrition_female) as nutrition_female,
                                                            SUM(nutrition_total) as nutrition_total,
                                                            
                                                            
                                                            SUM(dot_male) as dot_male,
                                                            SUM(dot_female) as dot_female,
                                                            SUM(dot_total) as dot_total,
                                                            
                                                            
                                                            SUM(tsod_male) as tsod_male,
                                                            SUM(tsod_female) as tsod_female,
                                                            SUM(tsod_total) as tsod_total,
                                                            
                                                            
                                                            SUM(d_e_male) as d_e_male,
                                                            SUM(d_e_female) as d_e_female,
                                                            SUM(d_e_total) as d_e_total,
                                                            
                                                            
                                                            SUM(d_s_male) as d_s_male,
                                                            SUM(d_s_female) as d_s_female,
                                                            SUM(d_s_total) as d_s_total,
                                                            
                                                             
                                                            SUM(injection_male) as injection_male,
                                                            SUM(injection_female) as injection_female,
                                                            SUM(injection_total) as injection_total,
                                                            
                                                            
                                                            SUM(total_male) as total_male,
                                                            SUM(total_female) as total_female,
                                                            SUM(total_total) as total_total,added_by


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                    $data = MonthlyHospitalStatistics::select(DB::raw('SUM(gopd_a_a_male) as gopd_a_a_male,
                                                            SUM(gopd_a_a_female) as gopd_a_a_female,
                                                            SUM(gopd_a_a_total) as gopd_a_a_total,

                                                            SUM(g_a_p_male) as g_a_p_male,
                                                            SUM(g_a_p_female) as g_a_p_female,
                                                            SUM(g_a_p_total) as g_a_p_total,

                                                            SUM(gopd_a_e_male) as gopd_a_e_male,
                                                            SUM(gopd_a_e_female) as gopd_a_e_female,
                                                            SUM(gopd_a_e_total) as gopd_a_e_total,

                                                            SUM(anc_male) as anc_male,
                                                            SUM(anc_female) as anc_female,
                                                            SUM(anc_total) as anc_total,

                                                            SUM(g_o_male) as g_o_male,
                                                            SUM(g_o_female) as g_o_female,
                                                            SUM(g_o_total) as g_o_total,

                                                            SUM(post_male) as post_male,
                                                            SUM(post_female) as post_female,
                                                            SUM(post_total) as post_total,

                                                            SUM(family_p_male) as family_p_male,
                                                            SUM(family_p_female) as family_p_female,
                                                            SUM(family_p_total) as family_p_total,

                                                            SUM(operation_male) as operation_male,
                                                            SUM(operation_female) as operation_female,
                                                            SUM(operation_total) as operation_total,

                                                            SUM(sopd_male) as sopd_male,
                                                            SUM(sopd_female) as sopd_female,
                                                            SUM(sopd_total) as sopd_total,

                                                            SUM(blood_d_male) as blood_d_male,
                                                            SUM(blood_d_female) as blood_d_female,
                                                            SUM(blood_d_total) as blood_d_total,

                                                            SUM(immu_male) as immu_male,
                                                            SUM(immu_female) as immu_female,
                                                            SUM(immu_total) as immu_total,

                                                            SUM(mopd_male) as mopd_male,
                                                            SUM(mopd_female) as mopd_female,
                                                            SUM(mopd_total) as mopd_total,

                                                            SUM(hiv_male) as hiv_male,
                                                            SUM(hiv_female) as hiv_female,
                                                            SUM(hiv_total) as hiv_total,

                                                            SUM(orthopedic_male) as orthopedic_male,
                                                            SUM(orthopedic_female) as orthopedic_female,
                                                            SUM(orthopedic_total) as orthopedic_total,

                                                            SUM(optometric_male) as optometric_male,
                                                            SUM(optometric_female) as optometric_female,
                                                            SUM(optometric_total) as optometric_total,
                                                            
                                                            SUM(neurology_male) as neurology_male,
                                                            SUM(neurology_female) as neurology_female,
                                                            SUM(neurology_total) as neurology_total,
                                                            
                                                            
                                                            SUM(urology_male) as urology_male,
                                                            SUM(urology_female) as urology_female,
                                                            SUM(urology_total) as urology_total,
                                                            
                                                            
                                                            SUM(physiotherapy_male) as physiotherapy_male,
                                                            SUM(physiotherapy_female) as physiotherapy_female,
                                                            SUM(physiotherapy_total) as physiotherapy_total,
                                                            
                                                            
                                                            SUM(nutrition_male) as nutrition_male,
                                                            SUM(nutrition_female) as nutrition_female,
                                                            SUM(nutrition_total) as nutrition_total,
                                                            
                                                            
                                                            SUM(dot_male) as dot_male,
                                                            SUM(dot_female) as dot_female,
                                                            SUM(dot_total) as dot_total,
                                                            
                                                            
                                                            SUM(tsod_male) as tsod_male,
                                                            SUM(tsod_female) as tsod_female,
                                                            SUM(tsod_total) as tsod_total,
                                                            
                                                            
                                                            SUM(d_e_male) as d_e_male,
                                                            SUM(d_e_female) as d_e_female,
                                                            SUM(d_e_total) as d_e_total,
                                                            
                                                            
                                                            SUM(d_s_male) as d_s_male,
                                                            SUM(d_s_female) as d_s_female,
                                                            SUM(d_s_total) as d_s_total,
                                                            
                                                            
                                                            SUM(injection_male) as injection_male,
                                                            SUM(injection_female) as injection_female,
                                                            SUM(injection_total) as injection_total,
                                                            
                                                            
                                                            SUM(total_male) as total_male,
                                                            SUM(total_female) as total_female,
                                                            SUM(total_total) as total_total
                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
            }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        return view('reports.monthly-hospital-statistics', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'operations' => $operations,
            'postData' => $postData]);
    }

    public function ImmunizationClinic(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['operations'] != '')
            $operations =$postData['operations']; 
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' && $operations != ''){
                    $data = ImmunizationClinic::select(DB::raw('SUM(bcg_male) as bcg_male,
                                                            SUM(bcg_female) as bcg_female,
                                                            SUM(bcg_total) as bcg_total,

                                                            SUM(oral_polio_male) as oral_polio_male,
                                                            SUM(oral_polio_female) as oral_polio_female,
                                                            SUM(oral_polio_total) as oral_polio_total,

                                                            SUM(penta_male) as penta_male,
                                                            SUM(penta_female) as penta_female,
                                                            SUM(penta_total) as penta_total,

                                                            SUM(measles_male) as measles_male,
                                                            SUM(measles_female) as measles_female,
                                                            SUM(measles_total) as measles_total,

                                                            SUM(tt_male) as tt_male,
                                                            SUM(tt_female) as tt_female,
                                                            SUM(tt_total) as tt_total,

                                                            SUM(ipv_male) as ipv_male,
                                                            SUM(ipv_female) as ipv_female,
                                                            SUM(ipv_total) as ipv_total,

                                                            SUM(yellow_fever_male) as yellow_fever_male,
                                                            SUM(yellow_fever_female) as yellow_fever_female,
                                                            SUM(yellow_fever_total) as yellow_fever_total,

                                                            SUM(csm_male) as csm_male,
                                                            SUM(csm_female) as csm_female,
                                                            SUM(csm_total) as csm_total,

                                                            SUM(hbv_male) as hbv_male,
                                                            SUM(hbv_female) as hbv_female,
                                                            SUM(hbv_total) as hbv_total,

                                                            SUM(pcl_male) as pcl_male,
                                                            SUM(pcl_female) as pcl_female,
                                                            SUM(pcl_total) as pcl_total,

                                                            SUM(total_male) as total_male,
                                                            SUM(total_female) as total_female,
                                                            SUM(total_total) as total_total,added_by


                                                      '))
                                                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
           
                   foreach($data as $key => $value1){ 
                       $name = User::where('id',$value1->added_by)->first();
                       $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                             
                        }
                        
                   
               }else{
                     $data = ImmunizationClinic::select(DB::raw('SUM(bcg_male) as bcg_male,
                                                            SUM(bcg_female) as bcg_female,
                                                            SUM(bcg_total) as bcg_total,

                                                            SUM(oral_polio_male) as oral_polio_male,
                                                            SUM(oral_polio_female) as oral_polio_female,
                                                            SUM(oral_polio_total) as oral_polio_total,

                                                            SUM(penta_male) as penta_male,
                                                            SUM(penta_female) as penta_female,
                                                            SUM(penta_total) as penta_total,

                                                            SUM(measles_male) as measles_male,
                                                            SUM(measles_female) as measles_female,
                                                            SUM(measles_total) as measles_total,

                                                            SUM(tt_male) as tt_male,
                                                            SUM(tt_female) as tt_female,
                                                            SUM(tt_total) as tt_total,

                                                            SUM(ipv_male) as ipv_male,
                                                            SUM(ipv_female) as ipv_female,
                                                            SUM(ipv_total) as ipv_total,

                                                            SUM(yellow_fever_male) as yellow_fever_male,
                                                            SUM(yellow_fever_female) as yellow_fever_female,
                                                            SUM(yellow_fever_total) as yellow_fever_total,

                                                            SUM(csm_male) as csm_male,
                                                            SUM(csm_female) as csm_female,
                                                            SUM(csm_total) as csm_total,

                                                            SUM(hbv_male) as hbv_male,
                                                            SUM(hbv_female) as hbv_female,
                                                            SUM(hbv_total) as hbv_total,

                                                            SUM(pcl_male) as pcl_male,
                                                            SUM(pcl_female) as pcl_female,
                                                            SUM(pcl_total) as pcl_total,

                                                            SUM(total_male) as total_male,
                                                            SUM(total_female) as total_female,
                                                            SUM(total_total) as total_total
                                                      '))
                                                      ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                      ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
            }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
            if($hospitalname1 == '' && $operations != ''){
                $chartData = array();
                foreach($data as $key => $value){
                    if($operations == 'bcg' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->bcg_male, "Female" => $value->bcg_female, "Total" => $value->bcg_total);
                    }
                    if($operations == 'oral_polio' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->oral_polio_male, "Female" => $value->oral_polio_female, "Total" => $value->oral_polio_total);
                    }
                    if($operations == 'penta' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->penta_male, "Female" => $value->penta_female, "Total" => $value->penta_total);
                    }
                    if($operations == 'measles' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->measles_male, "Female" => $value->measles_female, "Total" => $value->measles_total);
                    }
                    if($operations == 'tt' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->tt_male, "Female" => $value->tt_female, "Total" => $value->tt_total);
                    }
                    if($operations == 'ipv' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->ipv_male, "Female" => $value->ipv_female, "Total" => $value->ipv_total);
                    }
                    if($operations == 'yellow_fever' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->yellow_fever_male, "Female" => $value->yellow_fever_female, "Total" => $value->yellow_fever_total);
                    }
                    if($operations == 'csm' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->csm_male, "Female" =>$value->csm_female, "Total" =>$value->csm_total);
                    }
                    if($operations == 'hbv' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->hbv_male, "Female" =>$value->hbv_female, "Total" =>$value->hbv_total);
                    }
                    if($operations == 'pcv' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->pcl_male, "Female" =>$value->pcl_female, "Total" =>$value->pcl_total);
                    }
                    if($operations == 'total_male' ){
                        $chartData[$value->hospital_name] = array("Male" => $value->total_male, "Female" =>$value->total_female, "Total" =>$value->total_total);
                    }
                }
            }else{
                $chartData = array();
                if($operations == '' || $operations == 'bcg' ){
                    $chartData['BCG'] = array("Male" => $data->bcg_male, "Female" => $data->bcg_female, "Total" => $data->bcg_total);
                }
                if($operations == '' || $operations == 'oral_polio' ){
                    $chartData['Oral Polio'] = array("Male" => $data->oral_polio_male, "Female" => $data->oral_polio_female, "Total" => $data->oral_polio_total);
                }
                if($operations == '' || $operations == 'penta' ){
                    $chartData['Penta'] = array("Male" => $data->penta_male, "Female" => $data->penta_female, "Total" => $data->penta_total);
                }
                if($operations == '' || $operations == 'measles' ){
                    $chartData['Measles'] = array("Male" => $data->measles_male, "Female" => $data->measles_female, "Total" => $data->measles_total);
                }
                if($operations == '' || $operations == 'tt' ){
                    $chartData['TT'] = array("Male" => $data->tt_male, "Female" => $data->tt_female, "Total" => $data->tt_total);
                }
                if($operations == '' || $operations == 'ipv' ){
                    $chartData['IPV'] = array("Male" => $data->ipv_male, "Female" => $data->ipv_female, "Total" => $data->ipv_total);
                }
                if($operations == '' || $operations == 'yellow_fever' ){
                    $chartData['Yellow Fever'] = array("Male" => $data->yellow_fever_male, "Female" => $data->yellow_fever_female, "Total" => $data->yellow_fever_total);
                }
                if($operations == '' || $operations == 'csm' ){
                    $chartData['CSM'] = array("Male" => $data->csm_male, "Female" => $data->csm_female, "Total" => $data->csm_total);
                }
                if($operations == '' || $operations == 'hbv' ){
                    $chartData['HBV'] = array("Male" => $data->hbv_male, "Female" => $data->hbv_female, "Total" => $data->hbv_total);
                }
                if($operations == '' || $operations == 'pcv' ){
                    $chartData['PCV'] = array("Male" => $data->pcl_male, "Female" => $data->pcl_female, "Total" => $data->pcl_total);
                }
                if($operations == '' || $operations == 'total_male' ){
                    $chartData['Total'] = array("Male" => $data->total_male, "Female" => $data->total_female, "Total" => $data->total_total);
                }
            }


            $labelArray = array_keys($chartData);
            $chartFinalData = array();
            $piechartData = array();
            $pieChartColor  = array();
            foreach($chartData as $key => $chart){
                $k = 0;
                foreach($chart as $index => $value){
                    if($index == "Total"){
                        $piechartData[$k][$key." ".$index] = $value;
                        $pieChartColor[] = $this->rndRGBColorCode();
                    }
                    if($index != "Total"){
                        if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                            $chartFinalData[$k]['data'][] = $value;
                        }else{
                            $chartFinalData[$k]['label'] = $index;
                            $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['data'][] = $value;
                            }
                        }
                    }
                    $k++;   
                }
            }
            
            if(!empty($piechartData)){
                $piechartData = array_values($piechartData);
                $piechartData = $piechartData[0]; 
            }else{
                $piechartData = array();
            }
            
            
        }

        return view('reports.immunization-clinic', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'operations' => $operations,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }
    
    public function malariaPreventionReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
                $postData['month_from'] = str_replace('/','-',$postData['month_from']);
                $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = MalariaPreventation::select(DB::raw('SUM(children_u5y_received_llin) as children_u5y_received_llin'))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            
                            ->first();
                   
                   
               }else{
                   $data = MalariaPreventation::select(DB::raw('SUM(children_u5y_received_llin) as children_u5y_received_llin'))
                   ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                   ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                               if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                               }
                                            })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        return view('reports.malaria-prevention', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function nutritionReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = Nutrition::select(DB::raw('SUM(children_0to59_mwt) as children_0to59_mwt,
                                                            SUM(children_0to59_mwbbl) as children_0to59_mwbbl,
                                                            SUM(children_0to6_rbebf) as children_0to6_rbebf,

                                                            SUM(children_6to11_mgva) as children_6to11_mgva,
                                                            SUM(children_12to59_mgva) as children_12to59_mgva,
                                                            SUM(children_12to59_mgdm) as children_12to59_mgdm,

                                                            SUM(children_lt5y_otp_sc) as children_lt5y_otp_sc,
                                                            SUM(children_lt5y_discharged) as children_lt5y_discharged,
                                                            SUM(children_admitted_cmam_program) as children_admitted_cmam_program,

                                                            SUM(children_defaulted_cmam_program) as children_defaulted_cmam_program'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    ->first();
           
               }else{
                     $data = Nutrition::select(DB::raw('SUM(children_0to59_mwt) as children_0to59_mwt,
                                                            SUM(children_0to59_mwbbl) as children_0to59_mwbbl,
                                                            SUM(children_0to6_rbebf) as children_0to6_rbebf,

                                                            SUM(children_6to11_mgva) as children_6to11_mgva,
                                                            SUM(children_12to59_mgva) as children_12to59_mgva,
                                                            SUM(children_12to59_mgdm) as children_12to59_mgdm,

                                                            SUM(children_lt5y_otp_sc) as children_lt5y_otp_sc,
                                                            SUM(children_lt5y_discharged) as children_lt5y_discharged,
                                                            SUM(children_admitted_cmam_program) as children_admitted_cmam_program,

                                                            SUM(children_defaulted_cmam_program) as children_defaulted_cmam_program'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        return view('reports.nutrition', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function imciReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = Imci::select(DB::raw('SUM(diarrhoea_nc_lt5y) as diarrhoea_nc_lt5y,
                                                            SUM(diarrhoea_nc_lt5y_gorp) as diarrhoea_nc_lt5y_gorp,
                                                            SUM(diarrhoea_nc_lt5y_gozs) as diarrhoea_nc_lt5y_gozs,

                                                            SUM(pneumonia_nc_lt5y) as pneumonia_nc_lt5y,
                                                            SUM(pneumonia_nc_lt5y_ga) as pneumonia_nc_lt5y_ga,
                                                            SUM(measles_nc_lt5y) as measles_nc_lt5y'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    ->first();
           
           
               }else{
                     $data = Imci::select(DB::raw('SUM(diarrhoea_nc_lt5y) as diarrhoea_nc_lt5y,
                                                            SUM(diarrhoea_nc_lt5y_gorp) as diarrhoea_nc_lt5y_gorp,
                                                            SUM(diarrhoea_nc_lt5y_gozs) as diarrhoea_nc_lt5y_gozs,

                                                            SUM(pneumonia_nc_lt5y) as pneumonia_nc_lt5y,
                                                            SUM(pneumonia_nc_lt5y_ga) as pneumonia_nc_lt5y_ga,
                                                            SUM(measles_nc_lt5y) as measles_nc_lt5y'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        return view('reports.imci', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function familyPlanningReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = FamilyPlanning::select(DB::raw('SUM(clients_counselled) as clients_counselled,
                                                            SUM(new_fp_acceptors) as new_fp_acceptors,
                                                            SUM(fp_ca_hct_services) as fp_ca_hct_services,

                                                            SUM(ir_fp_services_from_hct) as ir_fp_services_from_hct,
                                                            SUM(ir_fp_services_from_art) as ir_fp_services_from_art,
                                                            SUM(females_aged_15to49y_mc) as females_aged_15to49y_mc,

                                                            SUM(persons_given_oral_pills) as persons_given_oral_pills,
                                                            SUM(oral_pill_cycle_dispensed) as oral_pill_cycle_dispensed,
                                                            SUM(injectables_given) as injectables_given,
                                                            
                                                            SUM(iucd_inserted) as iucd_inserted,
                                                            SUM(implants_inserted) as implants_inserted,
                                                            SUM(sterilization) as sterilization,
                                                            
                                                            SUM(male_condoms_distributed) as male_condoms_distributed,
                                                            SUM(female_condoms_distributed) as female_condoms_distributed,
                                                            SUM(ir_fp_services_from_pmtct) as ir_fp_services_from_pmtct'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    
                    ->first();
                    
               }else{
                     $data = FamilyPlanning::select(DB::raw('SUM(clients_counselled) as clients_counselled,
                                                            SUM(new_fp_acceptors) as new_fp_acceptors,
                                                            SUM(fp_ca_hct_services) as fp_ca_hct_services,

                                                            SUM(ir_fp_services_from_hct) as ir_fp_services_from_hct,
                                                            SUM(ir_fp_services_from_art) as ir_fp_services_from_art,
                                                            SUM(females_aged_15to49y_mc) as females_aged_15to49y_mc,

                                                            SUM(persons_given_oral_pills) as persons_given_oral_pills,
                                                            SUM(oral_pill_cycle_dispensed) as oral_pill_cycle_dispensed,
                                                            SUM(injectables_given) as injectables_given,
                                                            
                                                            SUM(iucd_inserted) as iucd_inserted,
                                                            SUM(implants_inserted) as implants_inserted,
                                                            SUM(sterilization) as sterilization,
                                                            
                                                            SUM(male_condoms_distributed) as male_condoms_distributed,
                                                            SUM(female_condoms_distributed) as female_condoms_distributed,
                                                            SUM(ir_fp_services_from_pmtct) as ir_fp_services_from_pmtct'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        
        return view('reports.family-planning', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function referralsReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = Referrals::select(DB::raw('SUM(referral_in) as referral_in,
                                                            SUM(referral_out) as referral_out,
                                                            SUM(mcr_further_treatment) as mcr_further_treatment,

                                                            SUM(mcr_adverse_drug_reaction) as mcr_adverse_drug_reaction,
                                                            SUM(wro_pregnancy_related_complications) as wro_pregnancy_related_complications,
                                                            SUM(wsar_obstetric_fistula) as wsar_obstetric_fistula'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    
                    ->first();
                    
                   
               }else{
                     $data = Referrals::select(DB::raw('SUM(referral_in) as referral_in,
                                                            SUM(referral_out) as referral_out,
                                                            SUM(mcr_further_treatment) as mcr_further_treatment,

                                                            SUM(mcr_adverse_drug_reaction) as mcr_adverse_drug_reaction,
                                                            SUM(wro_pregnancy_related_complications) as wro_pregnancy_related_complications,
                                                            SUM(wsar_obstetric_fistula) as wsar_obstetric_fistula'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        
        return view('reports.referrals', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function immunizationReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
                    $data = Immunization::select(DB::raw('SUM(opv_0_birth) as opv_0_birth,
                                                            SUM(hep_b_0_birth ) as hep_b_0_birth ,
                                                            SUM(bcg) as bcg,
                                                            
                                                            SUM(opv_1) as opv_1,
                                                            SUM(hep_b_1) as hep_b_1,
                                                            SUM(penta_1) as penta_1,
                                                            
                                                            SUM(dpt_1) as dpt_1,
                                                            SUM(pcv_1) as pcv_1,
                                                            SUM(opv_2) as opv_2,
                                                            
                                                            SUM(hep_b_2 ) as hep_b_2 ,
                                                            SUM(penta_2) as penta_2,
                                                            SUM(dpt_2) as dpt_2,
                                                            
                                                            SUM(pcv_2) as pcv_2,
                                                            SUM(opv_3) as opv_3,
                                                            SUM(penta_3) as penta_3,
                                                            
                                                            SUM(dpt_3) as dpt_3,
                                                            SUM(pcv_3) as pcv_3,
                                                            SUM(measles_1) as measles_1,
                                                            
                                                            SUM(fully_immunized_l1_year) as fully_immunized_l1_year,
                                                            

                                                            SUM(yellow_fever) as yellow_fever,
                                                            SUM(measles_2) as measles_2,
                                                            SUM(conjugate_a_csm) as conjugate_a_csm'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    ->first();
                   
                        
                      
                   
               }else{
                     $data = Immunization::select(DB::raw('SUM(opv_0_birth) as opv_0_birth,
                                                            SUM(hep_b_0_birth ) as hep_b_0_birth ,
                                                            SUM(bcg) as bcg,
                                                            
                                                            SUM(opv_1) as opv_1,
                                                            SUM(hep_b_1) as hep_b_1,
                                                            SUM(penta_1) as penta_1,
                                                            
                                                            SUM(dpt_1) as dpt_1,
                                                            SUM(pcv_1) as pcv_1,
                                                            SUM(opv_2) as opv_2,
                                                            
                                                            SUM(hep_b_2 ) as hep_b_2 ,
                                                            SUM(penta_2) as penta_2,
                                                            SUM(dpt_2) as dpt_2,
                                                            
                                                            SUM(pcv_2) as pcv_2,
                                                            SUM(opv_3) as opv_3,
                                                            SUM(penta_3) as penta_3,
                                                            
                                                            SUM(dpt_3) as dpt_3,
                                                            SUM(pcv_3) as pcv_3,
                                                            SUM(measles_1) as measles_1,
                                                            
                                                            SUM(fully_immunized_l1_year) as fully_immunized_l1_year,
                                                            

                                                            SUM(yellow_fever) as yellow_fever,
                                                            SUM(measles_2) as measles_2,
                                                            SUM(conjugate_a_csm) as conjugate_a_csm'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        
        return view('reports.immunization', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function nonCommunicableDiseasesReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);

        $request->flash();
        $postData = $request->all();
        $data = array();

        $hospital_name = Hospitals::get();
        $hospitalname1= '';
        $hospital_name3=[];
        //  echo '<pre>'; print_r($postData); exit;
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
           if (isset($postData) && count($postData) > 0) {
               if($hospitalname1 == '' ){
        //  echo '<pre>'; print_r(1); exit;
                    $data = NonCommunicableDiseases::select(DB::raw('SUM(coronary_heart_disease_nc) as coronary_heart_disease_nc,
                                                            SUM(diabetes_mellitus_nc ) as diabetes_mellitus_nc ,
                                                            SUM(hypertension_nc) as hypertension_nc,
                                                            
                                                            SUM(sickle_cell_disease_nc) as sickle_cell_disease_nc,
                                                            SUM(road_traffic_accident_nc) as road_traffic_accident_nc,
                                                            SUM(home_accident_nc) as home_accident_nc,
                                                            
                                                            SUM(snake_bites_nc) as snake_bites_nc,
                                                            SUM(asthma_nc ) as asthma_nc ,
                                                            SUM(athritis_nc) as athritis_nc'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                   //->where('year', $postData['year'])

                    ->first();
                   
                   
               }else{
                     $data = NonCommunicableDiseases::select(DB::raw('SUM(coronary_heart_disease_nc) as coronary_heart_disease_nc,
                                                            SUM(diabetes_mellitus_nc ) as diabetes_mellitus_nc ,
                                                            SUM(hypertension_nc) as hypertension_nc,
                                                            
                                                            SUM(sickle_cell_disease_nc) as sickle_cell_disease_nc,
                                                            SUM(road_traffic_accident_nc) as road_traffic_accident_nc,
                                                            SUM(home_accident_nc) as home_accident_nc,
                                                            
                                                            SUM(snake_bites_nc) as snake_bites_nc,
                                                            SUM(asthma_nc ) as asthma_nc ,
                                                            SUM(athritis_nc) as athritis_nc'))
                                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                           if($hospitalname1 != ''){
                                                $q->whereIn('added_by', $hospital_name3);
                                           }
                                        })
                            ->first();
               }
       
        }
         $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        
        return view('reports.non-communicable-diseases', [
            'data' => $data,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospital_display_name,
            'hospital_display_name' => $hospital_display_name,
            'years' => $years,
            'postData' => $postData]);
    }

    public function SearchRecordOfficeAggregatedReport(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $immunization_report = array();
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
       
        
        //Immunization report Code start
        $immunization = array();

       //  inpatient-records
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        
        $inpatient_record = array();
        $aande_data = array();
        $general_outpatient = array();
        $laboratory_investigations = array();
        $operations = array();
        $special_consultive_clinics	= array();
        $radio_diagnosis = array();
        $maternity_returns = array();
        $monthly_hospital_statistics = array();
        $immunization_clinic = array();
        $family_planning = array();
        $communicable_disease = array();
        $total_facility_attendance = array();
        $health_insurance = array();
        if(isset($postData) && count($postData) > 0 ){
            $hospitalname1 =$postData['hospitalname'];
            $inpatient_record = InpatientRecords::select(DB::raw('SUM(admission_male) as admission_male,
                                                        SUM(admission_female) as admission_female,
                                                        SUM(admission_total) as admission_total,
                                                        SUM(discharges_male) as discharges_male,
                                                        SUM(discharges_female) as discharges_female,
                                                        SUM(discharges_total) as discharges_total,
                                                        SUM(death_male) as death_male,
                                                        SUM(death_female) as death_female,
                                                        SUM(death_total) as death_total'))
                                                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                          ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                    if($hospitalname1 != ''){
                                        $q->whereIn('added_by', $hospital_name3);
                                    }
                                })
                    ->first();

            $aande_data = AccidentEmergency::select(DB::raw('SUM(rta_cases_male) as rta_cases_male,
                        SUM(rta_cases_female) as rta_cases_female,
                        SUM(rta_cases_total) as rta_cases_total,
                        SUM(dressing_a_e_male) as dressing_a_e_male,
                        SUM(dressing_a_e_female) as dressing_a_e_female,
                        SUM(dressing_a_e_total) as dressing_a_e_total,
                        SUM(dressing_sopd_male) as dressing_sopd_male,
                        SUM(dressing_sopd_female) as dressing_sopd_female,
                        SUM(dressing_sopd_total) as dressing_sopd_total,
                        SUM(injection_male) as injection_male,
                        SUM(injection_female) as injection_female,
                        SUM(injection_total) as injection_total,
                        SUM(a_e_attendance_male) as a_e_attendance_male,
                        SUM(a_e_attendance_female) as a_e_attendance_female,
                        SUM(a_e_attendance_total) as a_e_attendance_total,
                        SUM(epu_attendance_male) as epu_attendance_male,
                        SUM(epu_attendance_female) as epu_attendance_female,
                        SUM(epu_attendance_total) as epu_attendance_total,
                        SUM(bid_male) as bid_male,
                        SUM(bid_female) as bid_female,
                        SUM(bid_total) as bid_total
                    '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                        if($hospitalname1 != ''){
                            $q->whereIn('added_by', $hospital_name3);
                        }
                    })
                ->first();

               

            $general_outpatient = GeneralOutpatient::select(DB::raw('SUM(gopd_attendance_adult_male) as gopd_attendance_adult_male,
                                                    SUM(gopd_attendance_adult_female) as gopd_attendance_adult_female,
                                                    SUM(gopd_attendance_adult_total) as gopd_attendance_adult_total,
                                                    
                                                    SUM(gopd_attendance_pediatrics_male) as gopd_attendance_pediatrics_male,
                                                    SUM(gopd_attendance_pediatrics_female) as gopd_attendance_pediatrics_female,
                                                    SUM(gopd_attendance_pediatrics_total) as gopd_attendance_pediatrics_total,
                                                    
                                                    SUM(medical_corticated_fitness_male) as medical_corticated_fitness_male,
                                                    SUM(medical_corticated_fitness_female) as medical_corticated_fitness_female,
                                                    SUM(medical_corticated_fitness_total) as medical_corticated_fitness_total,
                                                    
                                                    SUM(maternity_leave_male) as maternity_leave_male,
                                                    SUM(maternity_leave_female) as maternity_leave_female,
                                                    SUM(maternity_leave_total) as maternity_leave_total,
                                                    
                                                    SUM(antenatal_attendance_male) as antenatal_attendance_male,
                                                    SUM(antenatal_attendance_female) as antenatal_attendance_female,
                                                    SUM(antenatal_attendance_total) as antenatal_attendance_total,
                                                    
                                                    SUM(postnatal_attendance_male) as postnatal_attendance_male,
                                                    SUM(postnatal_attendance_female) as postnatal_attendance_female,
                                                    SUM(postnatal_attendance_total) as postnatal_attendance_total,

                                                    SUM(nhis_male) as nhis_male,
                                                    SUM(nhis_female) as nhis_female,
                                                    SUM(nhis_total) as nhis_total,

                                                    SUM(fhis_male) as fhis_male,
                                                    SUM(fhis_female) as fhis_female,
                                                    SUM(fhis_total) as fhis_total,
                                                    
                                                    SUM(medical_male) as medical_male,
                                                    SUM(medical_female) as medical_female,
                                                    SUM(medical_total) as medical_total,

                                                    SUM(death_male) as death_male,
                                                    SUM(death_female) as death_female,
                                                    SUM(death_total) as death_total,

                                                    SUM(antenatal_attendance_old_male) as antenatal_attendance_old_male,
                                                    SUM(antenatal_attendance_old_female) as antenatal_attendance_old_female,
                                                    SUM(antenatal_attendance_old_total) as antenatal_attendance_old_total,
                                                    
                                                    SUM(family_planning_attendance_male) as family_planning_attendance_male,
                                                    SUM(family_planning_attendance_female) as family_planning_attendance_female,
                                                    SUM(family_planning_attendance_total) as family_planning_attendance_total
                                              '))
                                              ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                              ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                           if($hospitalname1 != ''){
                                                                $q->whereIn('added_by', $hospital_name3);
                                                           }
                                                        })
                                            ->first();
                                            
            $laboratory_investigations = LaboratoryInvestigations::select(DB::raw('SUM(hematology_male) as hematology_male,
                                            SUM(hematology_female) as hematology_female,
                                            SUM(hematology_total) as hematology_total,

                                            SUM(parasitology_male) as parasitology_male,
                                            SUM(parasitology_female) as parasitology_female,
                                            SUM(parasitology_total) as parasitology_total,

                                            SUM(chemistry_male) as chemistry_male,
                                            SUM(chemistry_female) as chemistry_female,
                                            SUM(chemistry_total) as chemistry_total,

                                            SUM(microbiology_male) as microbiology_male,
                                            SUM(microbiology_female) as microbiology_female,
                                            SUM(microbiology_total) as microbiology_total,

                                            SUM(histology_male) as histology_male,
                                            SUM(histology_female) as histology_female,
                                            SUM(histology_total) as histology_total,

                                            SUM(cyto_male) as cyto_male,
                                            SUM(cyto_female) as cyto_female,
                                            SUM(cyto_total) as cyto_total,

                                            SUM(blood_transfusion_male) as blood_transfusion_male,
                                            SUM(blood_transfusion_female) as blood_transfusion_female,
                                            SUM(blood_transfusion_total) as blood_transfusion_total,

                                            SUM(blood_donation_male) as blood_donation_male,
                                            SUM(blood_donation_female) as blood_donation_female,
                                            SUM(blood_donation_total) as blood_donation_total
                                      '))
                                      ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                      ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                            if($hospitalname1 != ''){
                                    $q->whereIn('added_by', $hospital_name3);
                            }
                            })
                ->first();

            
            $operations = Operations::select(DB::raw('SUM(major_operation_male) as major_operation_male,
                                                        SUM(major_operation_female) as major_operation_female,
                                                        SUM(major_operation_total) as major_operation_total,

                                                        SUM(intermediate_operation_male) as intermediate_operation_male,
                                                        SUM(intermediate_operation_female) as intermediate_operation_female,
                                                        SUM(intermediate_operation_total) as intermediate_operation_total,

                                                        SUM(minor_operation_male) as minor_operation_male,
                                                        SUM(minor_operation_female) as minor_operation_female,
                                                        SUM(minor_operation_total) as minor_operation_total,

                                                        SUM(circumcision_male) as circumcision_male,
                                                        SUM(circumcision_female) as circumcision_female,
                                                        SUM(circumcision_total) as circumcision_total


                                                  '))
                                                  ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                  ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                       if($hospitalname1 != ''){
                                            $q->whereIn('added_by', $hospital_name3);
                                       }
                                    })
                        ->first();
           
           
            $special_consultive_clinics = SpecialConsultiveClinics::select(DB::raw('SUM(obstetrics_gynecology_clinic_male) as obstetrics_gynecology_clinic_male,
                                                         SUM(obstetrics_gynecology_clinic_female) as obstetrics_gynecology_clinic_female,
                                                         SUM(obstetrics_gynecology_clinic_total) as obstetrics_gynecology_clinic_total,

                                                         SUM(surgical_clinic_male) as surgical_clinic_male,
                                                         SUM(surgical_clinic_female) as surgical_clinic_female,
                                                         SUM(surgical_clinic_total) as surgical_clinic_total,

                                                         SUM(medical_clinic_male) as medical_clinic_male,
                                                         SUM(medical_clinic_female) as medical_clinic_female,
                                                         SUM(medical_clinic_total) as medical_clinic_total,

                                                         SUM(ear_nose_throat_clinic_male) as ear_nose_throat_clinic_male,
                                                         SUM(ear_nose_throat_clinic_female) as ear_nose_throat_clinic_female,
                                                         SUM(ear_nose_throat_clinic_total) as ear_nose_throat_clinic_total,

                                                         SUM(dental_clinic_male) as dental_clinic_male,
                                                         SUM(dental_clinic_female) as dental_clinic_female,
                                                         SUM(dental_clinic_total) as dental_clinic_total,

                                                         SUM(ophthalmology_clinic_male) as ophthalmology_clinic_male,
                                                         SUM(ophthalmology_clinic_female) as ophthalmology_clinic_female,
                                                         SUM(ophthalmology_clinic_total) as ophthalmology_clinic_total,

                                                         SUM(optometric_clinic_male) as optometric_clinic_male,
                                                         SUM(optometric_clinic_female) as optometric_clinic_female,
                                                         SUM(optometric_clinic_total) as optometric_clinic_total,

                                                         SUM(urology_clinic_male) as urology_clinic_male,
                                                         SUM(urology_clinic_female) as urology_clinic_female,
                                                         SUM(urology_clinic_total) as urology_clinic_total,

                                                         SUM(orthopedics_clinic_male) as orthopedics_clinic_male,
                                                         SUM(orthopedics_clinic_female) as orthopedics_clinic_female,
                                                         SUM(orthopedics_clinic_total) as orthopedics_clinic_total,

                                                         SUM(pediatrics_clinic_male) as pediatrics_clinic_male,
                                                         SUM(pediatrics_clinic_female) as pediatrics_clinic_female,
                                                         SUM(pediatrics_clinic_total) as pediatrics_clinic_total,

                                                         SUM(physiotherapy_clinic_male) as physiotherapy_clinic_male,
                                                         SUM(physiotherapy_clinic_female) as physiotherapy_clinic_female,
                                                         SUM(physiotherapy_clinic_total) as physiotherapy_clinic_total,

                                                         SUM(comprehensive_site_male) as comprehensive_site_male,
                                                         SUM(comprehensive_site_female) as comprehensive_site_female,
                                                         SUM(comprehensive_site_total) as comprehensive_site_total,

                                                         SUM(neurology_clinic_male) as neurology_clinic_male,
                                                         SUM(neurology_clinic_female) as neurology_clinic_female,
                                                         SUM(neurology_clinic_total) as neurology_clinic_total,

                                                         SUM(nutrition_clinic_male) as nutrition_clinic_male,
                                                         SUM(nutrition_clinic_female) as nutrition_clinic_female,
                                                         SUM(nutrition_clinic_total) as nutrition_clinic_total,

                                                         SUM(dot_clinic_male) as dot_clinic_male,
                                                         SUM(dot_clinic_female) as dot_clinic_female,
                                                         SUM(dot_clinic_total) as dot_clinic_total,

                                                         SUM(peadiatrics_surgery_male) as peadiatrics_surgery_male,
                                                         SUM(peadiatrics_surgery_female) as peadiatrics_surgery_female,
                                                         SUM(peadiatrics_surgery_total) as peadiatrics_surgery_total,

                                                         SUM(dialysis_male) as dialysis_male,
                                                         SUM(dialysis_female) as dialysis_female,
                                                         SUM(dialysis_total) as dialysis_total,

                                                         SUM(total_dialysis_male) as total_dialysis_male,
                                                         SUM(total_dialysis_female) as total_dialysis_female,
                                                         SUM(total_dialysis_total) as total_dialysis_total,

                                                         SUM(dermatology_male) as dermatology_male,
                                                         SUM(dermatology_female) as dermatology_female,
                                                         SUM(dermatology_total) as dermatology_total,

                                                         SUM(pyschiatric_male) as pyschiatric_male,
                                                         SUM(pyschiatric_female) as pyschiatric_female,
                                                         SUM(pyschiatric_total) as pyschiatric_total,

                                                         SUM(plastic_male) as plastic_male,
                                                         SUM(plastic_female) as plastic_female,
                                                         SUM(plastic_total) as plastic_total


                                                   '))
                                                   ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                   ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                         ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                        if($hospitalname1 != ''){
                                             $q->whereIn('added_by', $hospital_name3);
                                        }
                                     })
                         ->first();
        
            
            $radio_diagnosis = RadioDiagnosis::select(DB::raw('SUM(contrast_x_ray_inpatients_male) as contrast_x_ray_inpatients_male,
                                                    SUM(contrast_x_ray_inpatients_female) as contrast_x_ray_inpatients_female,
                                                    SUM(contrast_x_ray_inpatients_total) as contrast_x_ray_inpatients_total,

                                                    SUM(contrast_x_ray_outpatients_male) as contrast_x_ray_outpatients_male,
                                                    SUM(contrast_x_ray_outpatients_female) as contrast_x_ray_outpatients_female,
                                                    SUM(contrast_x_ray_outpatients_total) as contrast_x_ray_outpatients_total,

                                                    SUM(ultrasound_inpatients_male) as ultrasound_inpatients_male,
                                                    SUM(ultrasound_inpatients_female) as ultrasound_inpatients_female,
                                                    SUM(ultrasound_inpatients_total) as ultrasound_inpatients_total,

                                                    SUM(ultrasound_outpatients_male) as ultrasound_outpatients_male,
                                                    SUM(ultrasound_outpatients_female) as ultrasound_outpatients_female,
                                                    SUM(ultrasound_outpatients_total) as ultrasound_outpatients_total,

                                                    SUM(ecg_male) as ecg_male,
                                                    SUM(ecg_female) as ecg_female,
                                                    SUM(ecg_total) as ecg_total,

                                                    SUM(echo_male) as echo_male,
                                                    SUM(echo_female) as echo_female,
                                                    SUM(echo_total) as echo_total,

                                                    SUM(mamogramph_male) as mamogramph_male,
                                                    SUM(mamogramph_female) as mamogramph_female,
                                                    SUM(mamogramph_total) as mamogramph_total,

                                                    SUM(ct_scan_male) as ct_scan_male,
                                                    SUM(ct_scan_female) as ct_scan_female,
                                                    SUM(ct_scan_total) as ct_scan_total,

                                                    SUM(hsg_male) as hsg_male,
                                                    SUM(hsg_female) as hsg_female,
                                                    SUM(hsg_total) as hsg_total


                                            '))
                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                    if($hospitalname1 != ''){
                                        $q->whereIn('added_by', $hospital_name3);
                                    }
                                })
                    ->first();
        
        
            $maternity_returns = MaternityReturns::select(DB::raw('SUM(live_birth_male) as live_birth_male,
                                                             SUM(live_birth_female) as live_birth_female,
                                                             SUM(live_birth_total) as live_birth_total,
 
                                                             SUM(fresh_still_birth_male) as fresh_still_birth_male,
                                                             SUM(fresh_still_birth_female) as fresh_still_birth_female,
                                                             SUM(fresh_still_birth_total) as fresh_still_birth_total,
 
                                                             SUM(macerated_still_birth_male) as macerated_still_birth_male,
                                                             SUM(macerated_still_birth_female) as macerated_still_birth_female,
                                                             SUM(macerated_still_birth_total) as macerated_still_birth_total,
 
                                                             SUM(asphyxia_male) as asphyxia_male,
                                                             SUM(asphyxia_female) as asphyxia_female,
                                                             SUM(asphyxia_total) as asphyxia_total,
 
                                                             SUM(low_birth_weight_male) as low_birth_weight_male,
                                                             SUM(low_birth_weight_female) as low_birth_weight_female,
                                                             SUM(low_birth_weight_total) as low_birth_weight_total,
 
                                                             SUM(macrosomic_babies_male) as macrosomic_babies_male,
                                                             SUM(macrosomic_babies_female) as macrosomic_babies_female,
                                                             SUM(macrosomic_babies_total) as macrosomic_babies_total,
 
                                                             SUM(immediate_neo_natal_death_male) as immediate_neo_natal_death_male,
                                                             SUM(immediate_neo_natal_death_female) as immediate_neo_natal_death_female,
                                                             SUM(immediate_neo_natal_death_total) as immediate_neo_natal_death_total,
 
                                                             SUM(born_before_arrival_male) as born_before_arrival_male,
                                                             SUM(born_before_arrival_female) as born_before_arrival_female,
                                                             SUM(born_before_arrival_total) as born_before_arrival_total,
 
                                                             SUM(pre_maturity_male) as pre_maturity_male,
                                                             SUM(pre_maturity_female) as pre_maturity_female,
                                                             SUM(pre_maturity_total) as pre_maturity_total,
 
                                                             SUM(booked_cases_total) as booked_cases_total,
                                                             SUM(unbooked_cases_total) as unbooked_cases_total,
                                                             SUM(svd_total) as svd_total,
                                                             
                                                             SUM(elective_c_s_total) as elective_c_s_total,
                                                             SUM(emergency_c_s_total) as emergency_c_s_total,
                                                             SUM(breech_delivery_total) as breech_delivery_total,
                                                             
                                                             SUM(twin_delivery_total) as twin_delivery_total,
                                                             SUM(vacum_delivery_total) as vacum_delivery_total,
                                                             SUM(forceps_delivery_total) as forceps_delivery_total,
                                                             
                                                             SUM(induction_of_labour_total) as induction_of_labour_total,
                                                             SUM(preterm_labour_total) as preterm_labour_total,
                                                             SUM(manual_removal_of_placenta_total) as manual_removal_of_placenta_total,
                                                             
                                                             SUM(pph_post_partum_total) as pph_post_partum_total,
                                                             SUM(prm_total) as prm_total,
                                                             SUM(aph_total) as aph_total,
                                                             
                                                             SUM(placenta_previa_total) as placenta_previa_total,
                                                             SUM(abruption_placenta_total) as abruption_placenta_total,
                                                             SUM(pre_eclampsia_total) as pre_eclampsia_total,
                                                             
                                                             SUM(eclampsia_total) as eclampsia_total,
                                                             SUM(iufd_total) as iufd_total,
                                                             SUM(maternal_death_total) as maternal_death_total,
                                                             SUM(mva) as mva,
                                                             SUM(total_no_deliveries) as total_no_deliveries
 
 
                                                       '))
                                                       ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                       ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                             ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                            if($hospitalname1 != ''){
                                                 $q->whereIn('added_by', $hospital_name3);
                                            }
                                         })
                             ->first();
               
                                        // echo '<pre>'; print_r($maternity_returns); exit;
            
            $monthly_hospital_statistics = MonthlyHospitalStatistics::select(DB::raw('SUM(gopd_a_a_male) as gopd_a_a_male,
                                                             SUM(gopd_a_a_female) as gopd_a_a_female,
                                                             SUM(gopd_a_a_total) as gopd_a_a_total,
 
                                                             SUM(g_a_p_male) as g_a_p_male,
                                                             SUM(g_a_p_female) as g_a_p_female,
                                                             SUM(g_a_p_total) as g_a_p_total,
 
                                                             SUM(gopd_a_e_male) as gopd_a_e_male,
                                                             SUM(gopd_a_e_female) as gopd_a_e_female,
                                                             SUM(gopd_a_e_total) as gopd_a_e_total,
 
                                                             SUM(anc_male) as anc_male,
                                                             SUM(anc_female) as anc_female,
                                                             SUM(anc_total) as anc_total,
 
                                                             SUM(g_o_male) as g_o_male,
                                                             SUM(g_o_female) as g_o_female,
                                                             SUM(g_o_total) as g_o_total,
 
                                                             SUM(post_male) as post_male,
                                                             SUM(post_female) as post_female,
                                                             SUM(post_total) as post_total,
 
                                                             SUM(family_p_male) as family_p_male,
                                                             SUM(family_p_female) as family_p_female,
                                                             SUM(family_p_total) as family_p_total,
 
                                                             SUM(operation_male) as operation_male,
                                                             SUM(operation_female) as operation_female,
                                                             SUM(operation_total) as operation_total,
 
                                                             SUM(sopd_male) as sopd_male,
                                                             SUM(sopd_female) as sopd_female,
                                                             SUM(sopd_total) as sopd_total,
 
                                                             SUM(blood_d_male) as blood_d_male,
                                                             SUM(blood_d_female) as blood_d_female,
                                                             SUM(blood_d_total) as blood_d_total,
 
                                                             SUM(immu_male) as immu_male,
                                                             SUM(immu_female) as immu_female,
                                                             SUM(immu_total) as immu_total,
 
                                                             SUM(mopd_male) as mopd_male,
                                                             SUM(mopd_female) as mopd_female,
                                                             SUM(mopd_total) as mopd_total,
 
                                                             SUM(hiv_male) as hiv_male,
                                                             SUM(hiv_female) as hiv_female,
                                                             SUM(hiv_total) as hiv_total,
 
                                                             SUM(orthopedic_male) as orthopedic_male,
                                                             SUM(orthopedic_female) as orthopedic_female,
                                                             SUM(orthopedic_total) as orthopedic_total,
 
                                                             SUM(optometric_male) as optometric_male,
                                                             SUM(optometric_female) as optometric_female,
                                                             SUM(optometric_total) as optometric_total,
                                                             
                                                             SUM(neurology_male) as neurology_male,
                                                             SUM(neurology_female) as neurology_female,
                                                             SUM(neurology_total) as neurology_total,
                                                             
                                                             
                                                             SUM(urology_male) as urology_male,
                                                             SUM(urology_female) as urology_female,
                                                             SUM(urology_total) as urology_total,
                                                             
                                                             
                                                             SUM(physiotherapy_male) as physiotherapy_male,
                                                             SUM(physiotherapy_female) as physiotherapy_female,
                                                             SUM(physiotherapy_total) as physiotherapy_total,
                                                             
                                                             
                                                             SUM(nutrition_male) as nutrition_male,
                                                             SUM(nutrition_female) as nutrition_female,
                                                             SUM(nutrition_total) as nutrition_total,
                                                             
                                                             
                                                             SUM(dot_male) as dot_male,
                                                             SUM(dot_female) as dot_female,
                                                             SUM(dot_total) as dot_total,
                                                             
                                                             
                                                             SUM(tsod_male) as tsod_male,
                                                             SUM(tsod_female) as tsod_female,
                                                             SUM(tsod_total) as tsod_total,
                                                             
                                                             
                                                             SUM(d_e_male) as d_e_male,
                                                             SUM(d_e_female) as d_e_female,
                                                             SUM(d_e_total) as d_e_total,
                                                             
                                                             
                                                             SUM(d_s_male) as d_s_male,
                                                             SUM(d_s_female) as d_s_female,
                                                             SUM(d_s_total) as d_s_total,
                                                             
                                                             
                                                             SUM(injection_male) as injection_male,
                                                             SUM(injection_female) as injection_female,
                                                             SUM(injection_total) as injection_total,
                                                             
                                                             
                                                             SUM(total_male) as total_male,
                                                             SUM(total_female) as total_female,
                                                             SUM(total_total) as total_total
                                                       '))
                                                       ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                       ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                     ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                            if($hospitalname1 != ''){
                                                 $q->whereIn('added_by', $hospital_name3);
                                            }
                                         })
                             ->first();

              
            $immunization_clinic = ImmunizationClinic::select(DB::raw('SUM(bcg_male) as bcg_male,
                                                             SUM(bcg_female) as bcg_female,
                                                             SUM(bcg_total) as bcg_total,
 
                                                             SUM(oral_polio_male) as oral_polio_male,
                                                             SUM(oral_polio_female) as oral_polio_female,
                                                             SUM(oral_polio_total) as oral_polio_total,
 
                                                             SUM(penta_male) as penta_male,
                                                             SUM(penta_female) as penta_female,
                                                             SUM(penta_total) as penta_total,
 
                                                             SUM(measles_male) as measles_male,
                                                             SUM(measles_female) as measles_female,
                                                             SUM(measles_total) as measles_total,
 
                                                             SUM(tt_male) as tt_male,
                                                             SUM(tt_female) as tt_female,
                                                             SUM(tt_total) as tt_total,
 
                                                             SUM(ipv_male) as ipv_male,
                                                             SUM(ipv_female) as ipv_female,
                                                             SUM(ipv_total) as ipv_total,
 
                                                             SUM(yellow_fever_male) as yellow_fever_male,
                                                             SUM(yellow_fever_female) as yellow_fever_female,
                                                             SUM(yellow_fever_total) as yellow_fever_total,
 
                                                             SUM(csm_male) as csm_male,
                                                             SUM(csm_female) as csm_female,
                                                             SUM(csm_total) as csm_total,
 
                                                             SUM(hbv_male) as hbv_male,
                                                             SUM(hbv_female) as hbv_female,
                                                             SUM(hbv_total) as hbv_total,
 
                                                             SUM(pcl_male) as pcl_male,
                                                             SUM(pcl_female) as pcl_female,
                                                             SUM(pcl_total) as pcl_total,
 
                                                             SUM(total_male) as total_male,
                                                             SUM(total_female) as total_female,
                                                             SUM(total_total) as total_total
                                                       '))
                                                       ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                       ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                    ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                        if($hospitalname1 != ''){
                                            $q->whereIn('added_by', $hospital_name3);
                                        }
                                    })
                                    ->first();
              
                if(isset($postData) && count($postData) > 0 ){
                    $to = date('Y-m-d',strtotime("+1 day", strtotime($postData['month_to'])));
                    $from = date('Y-m-d',strtotime("-1 day", strtotime($postData['month_from'])));
                }
                                    
            $family_planning = FamilyPlanningRecordOffice::select(DB::raw('SUM(new_family_planning_acceptors_dispensed) as new_family_planning_acceptors_dispensed,
                                                SUM(new_family_planning_acceptors_used) as new_family_planning_acceptors_used,
                                                SUM(new_family_planning_acceptors_total) as new_family_planning_acceptors_total,
                                                SUM(depo_provera_Injection_dispensed) as depo_provera_Injection_dispensed,
                                                SUM(depo_provera_Injection_used) as depo_provera_Injection_used,
                                                SUM(depo_provera_Injection_total) as depo_provera_Injection_total,
                                                SUM(exlution_microlut_dispensed) as exlution_microlut_dispensed,
                                                SUM(exlution_microlut_used) as exlution_microlut_used,
                                                SUM(exlution_microlut_total) as exlution_microlut_total,
                                                SUM(iucd_dispensed) as iucd_dispensed,
                                                SUM(iucd_used) as iucd_used,
                                                SUM(iucd_total) as iucd_total,
                                                SUM(lo_feminal_dispensed) as lo_feminal_dispensed,
                                                SUM(lo_feminal_used) as lo_feminal_used,
                                                SUM(lo_feminal_total) as lo_feminal_total,
                                                SUM(microgynon_dispensed) as microgynon_dispensed,
                                                SUM(microgynon_used) as microgynon_used,
                                                SUM(microgynon_total) as microgynon_total,
                                                SUM(noristerat_dispensed) as noristerat_dispensed,
                                                SUM(noristerat_used) as noristerat_used,
                                                SUM(noristerat_total) as noristerat_total,
                                                SUM(implanon_dispensed) as implanon_dispensed,
                                                SUM(implanon_used) as implanon_used,
                                                SUM(implanon_total) as implanon_total,
                                                SUM(jardelle_dispensed) as jardelle_dispensed,
                                                SUM(jardelle_used) as jardelle_used,
                                                SUM(jardelle_total) as jardelle_total,
                                                SUM(condom_male_and_female_dispensed) as condom_male_and_female_dispensed,
                                                SUM(condom_male_and_female_used) as condom_male_and_female_used,
                                                SUM(condom_male_and_female_total) as condom_male_and_female_total
                                            '))
                                
                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                    if($hospitalname1 != ''){
                                            $q->whereIn('added_by', $hospital_name3);
                                    }
                                })
                                ->first();
            $communicable_disease = CommunicableDisease::select(DB::raw('SUM(new_malaria_cases_male) as new_malaria_cases_male,SUM(new_malaria_cases_female) as new_malaria_cases_female,SUM(new_malaria_cases_total) as new_malaria_cases_total,SUM(clinic_tested_malaria_male) as clinic_tested_malaria_male,SUM(clinic_tested_malaria_female) as clinic_tested_malaria_female,SUM(clinic_tested_malaria_total) as clinic_tested_malaria_total,SUM(malaria_cases_treated_with_act_male) as malaria_cases_treated_with_act_male,SUM(malaria_cases_treated_with_act_female) as malaria_cases_treated_with_act_female,SUM(malaria_cases_treated_with_act_total) as malaria_cases_treated_with_act_total,SUM(malaria_in_pregnancy_male) as malaria_in_pregnancy_male,SUM(malaria_in_pregnancy_female) as malaria_in_pregnancy_female,SUM(malaria_in_pregnancy_total) as malaria_in_pregnancy_total,SUM(drug_resistance_malaria_cases_male) as drug_resistance_malaria_cases_male,SUM(drug_resistance_malaria_cases_female) as drug_resistance_malaria_cases_female,SUM(drug_resistance_malaria_cases_total) as drug_resistance_malaria_cases_total,SUM(malaria_severe_male) as malaria_severe_male,SUM(malaria_severe_female) as malaria_severe_female,SUM(malaria_severe_total) as malaria_severe_total,SUM(number_of_new_hiv_cases_male) as number_of_new_hiv_cases_male,SUM(number_of_new_hiv_cases_female) as number_of_new_hiv_cases_female,SUM(number_of_new_hiv_cases_total) as number_of_new_hiv_cases_total,SUM(number_of_co_factor_cases_tb_hiv_male) as number_of_co_factor_cases_tb_hiv_male,SUM(number_of_co_factor_cases_tb_hiv_female) as number_of_co_factor_cases_tb_hiv_female,SUM(number_of_co_factor_cases_tb_hiv_total) as number_of_co_factor_cases_tb_hiv_total,SUM(number_of_hiv_persons_on_art_male) as number_of_hiv_persons_on_art_male,SUM(number_of_hiv_persons_on_art_female) as number_of_hiv_persons_on_art_female,SUM(number_of_hiv_persons_on_art_total) as number_of_hiv_persons_on_art_total,SUM(number_of_dropouts_male) as number_of_dropouts_male,SUM(number_of_dropouts_female) as number_of_dropouts_female,SUM(number_of_dropouts_total) as number_of_dropouts_total,SUM(death_complication_related_to_hiv_aids_male) as death_complication_related_to_hiv_aids_male,SUM(death_complication_related_to_hiv_aids_female) as death_complication_related_to_hiv_aids_female,SUM(death_complication_related_to_hiv_aids_total) as death_complication_related_to_hiv_aids_total,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_male) as new_case_of_severe_actute_respiratory_syndrome_sars_male,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_female) as new_case_of_severe_actute_respiratory_syndrome_sars_female,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as new_case_of_severe_actute_respiratory_syndrome_sars_total,SUM(new_case_of_severe_actute_respiratory_illness_sari_male) as new_case_of_severe_actute_respiratory_illness_sari_male,SUM(new_case_of_severe_actute_respiratory_illness_sari_female) as new_case_of_severe_actute_respiratory_illness_sari_female,SUM(new_case_of_severe_actute_respiratory_illness_sari_total) as new_case_of_severe_actute_respiratory_illness_sari_total,SUM(new_case_of_neonatal_tetanus_male) as new_case_of_neonatal_tetanus_male,SUM(new_case_of_neonatal_tetanus_female) as new_case_of_neonatal_tetanus_female,SUM(new_case_of_neonatal_tetanus_total) as new_case_of_neonatal_tetanus_total,SUM(new_case_of_measles_male) as new_case_of_measles_male,SUM(new_case_of_measles_female) as new_case_of_measles_female,SUM(new_case_of_measles_total) as new_case_of_measles_total,SUM(new_case_of_onchcerciasis_male) as new_case_of_onchcerciasis_male,SUM(new_case_of_onchcerciasis_female) as new_case_of_onchcerciasis_female,SUM(new_case_of_onchcerciasis_total) as new_case_of_onchcerciasis_total,SUM(new_case_of_poliomylitis_male) as new_case_of_poliomylitis_male,SUM(new_case_of_poliomylitis_female) as new_case_of_poliomylitis_female,SUM(new_case_of_poliomylitis_total) as new_case_of_poliomylitis_total,SUM(new_case_of_rabies_human_male) as new_case_of_rabies_human_male,SUM(new_case_of_rabies_human_female) as new_case_of_rabies_human_female,SUM(new_case_of_rabies_human_total) as new_case_of_rabies_human_total,SUM(new_case_of_smallpox_male) as new_case_of_smallpox_male,SUM(new_case_of_smallpox_female) as new_case_of_smallpox_female,SUM(new_case_of_smallpox_total) as new_case_of_smallpox_total,SUM(new_case_of_sexually_transmitted_infection_stis_male) as new_case_of_sexually_transmitted_infection_stis_male,SUM(new_case_of_sexually_transmitted_infection_stis_female) as new_case_of_sexually_transmitted_infection_stis_female,SUM(new_case_of_sexually_transmitted_infection_stis_total) as new_case_of_sexually_transmitted_infection_stis_total,SUM(new_case_of_yellow_fever_male) as new_case_of_yellow_fever_male,SUM(new_case_of_yellow_fever_female) as new_case_of_yellow_fever_female,SUM(new_case_of_yellow_fever_total) as new_case_of_yellow_fever_total,SUM(new_case_of_finding_of_tuberculosis_male) as new_case_of_finding_of_tuberculosis_male,SUM(new_case_of_finding_of_tuberculosis_female) as new_case_of_finding_of_tuberculosis_female,SUM(new_case_of_finding_of_tuberculosis_total) as new_case_of_finding_of_tuberculosis_total,SUM(tb_hiv_co_infection_case_male) as tb_hiv_co_infection_case_male,SUM(tb_hiv_co_infection_case_female) as tb_hiv_co_infection_case_female,SUM(tb_hiv_co_infection_case_total) as tb_hiv_co_infection_case_total,SUM(tb_patient_on_art_male) as tb_patient_on_art_male,SUM(tb_patient_on_art_female) as tb_patient_on_art_female,SUM(tb_patient_on_art_total) as tb_patient_on_art_total,SUM(multiple_drug_reaction_tb_cases_male) as multiple_drug_reaction_tb_cases_male,SUM(multiple_drug_reaction_tb_cases_female) as multiple_drug_reaction_tb_cases_female,SUM(multiple_drug_reaction_tb_cases_total) as multiple_drug_reaction_tb_cases_total,SUM(number_of_new_cases_covid_19_male) as number_of_new_cases_covid_19_male,SUM(number_of_new_cases_covid_19_female) as number_of_new_cases_covid_19_female,SUM(number_of_new_cases_covid_19_total) as number_of_new_cases_covid_19_total,SUM(clinic_tested_covid_19_male) as clinic_tested_covid_19_male,SUM(clinic_tested_covid_19_female) as clinic_tested_covid_19_female,SUM(clinic_tested_covid_19_total) as clinic_tested_covid_19_total,SUM(cases_reported_treated_covid_19_male) as cases_reported_treated_covid_19_male,SUM(cases_reported_treated_covid_19_female) as cases_reported_treated_covid_19_female,SUM(cases_reported_treated_covid_19_total) as cases_reported_treated_covid_19_total,SUM(drug_resistenace_covid_19_cases_male) as drug_resistenace_covid_19_cases_male,SUM(drug_resistenace_covid_19_cases_female) as drug_resistenace_covid_19_cases_female,SUM(drug_resistenace_covid_19_cases_total) as drug_resistenace_covid_19_cases_total,SUM(number_of_new_faces_lassa_fever_male) as number_of_new_faces_lassa_fever_male,SUM(number_of_new_faces_lassa_fever_female) as number_of_new_faces_lassa_fever_female,SUM(number_of_new_faces_lassa_fever_total) as number_of_new_faces_lassa_fever_total,SUM(clinic_tested_lassa_fever_male) as clinic_tested_lassa_fever_male,SUM(clinic_tested_lassa_fever_female) as clinic_tested_lassa_fever_female,SUM(clinic_tested_lassa_fever_total) as clinic_tested_lassa_fever_total,SUM(lassa_fever_cases_reported_treated_male) as lassa_fever_cases_reported_treated_male,SUM(lassa_fever_cases_reported_treated_female) as lassa_fever_cases_reported_treated_female,SUM(lassa_fever_cases_reported_treated_total) as lassa_fever_cases_reported_treated_total,SUM(drug_resistance_lassa_fever_cases_male) as drug_resistance_lassa_fever_cases_male,SUM(drug_resistance_lassa_fever_cases_female) as drug_resistance_lassa_fever_cases_female,SUM(drug_resistance_lassa_fever_cases_total) as drug_resistance_lassa_fever_cases_total,SUM(number_of_new_cases_of_cholera_male) as number_of_new_cases_of_cholera_male,SUM(number_of_new_cases_of_cholera_female) as number_of_new_cases_of_cholera_female,SUM(number_of_new_cases_of_cholera_total) as number_of_new_cases_of_cholera_total'))
                    
                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                            if($hospitalname1 != ''){
                                    $q->whereIn('added_by', $hospital_name3);
                            }
                        })
                        ->first(); 

            $total_facility_attendance = TotalFacilityAttendance::select(DB::raw('SUM(total_facility_attendance_male) as total_facility_attendance_male,
                                        SUM(total_facility_attendance_female) as total_facility_attendance_female,
                                        SUM(total_facility_attendance_total) as total_facility_attendance_total
                                        '))
                            
                                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                if($hospitalname1 != ''){
                                        $q->whereIn('added_by', $hospital_name3);
                                }
                            })
                        ->first(); 
                        
            $health_insurance = HealthInsurance::select(DB::raw('SUM(nhis_male) as nhis_male,SUM(nhis_female) as nhis_female,SUM(nhis_total) as nhis_total,SUM(fhis_male) as fhis_male,SUM(fhis_female) as fhis_female,SUM(fhis_total) as fhis_total,SUM(nhis_enrolled_male) as nhis_enrolled_male,SUM(nhis_enrolled_female) as nhis_enrolled_female,SUM(nhis_enrolled_total) as nhis_enrolled_total,SUM(fhis_enrolled_male) as fhis_enrolled_male,SUM(fhis_enrolled_female) as fhis_enrolled_female,SUM(fhis_enrolled_total) as fhis_enrolled_total,added_by,hospital_id'))
                            
                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                if($hospitalname1 != ''){
                                        $q->whereIn('added_by', $hospital_name3);
                                }
                            })
                        ->first();
        }

        return view('reports.record-office-aggregated-report', [
            'immunization' => $immunization,
            'inpatient_record' => $inpatient_record,
            'aande_data' => $aande_data,
            'general_outpatient' => $general_outpatient,
            'laboratory_investigations' => $laboratory_investigations,
            'operations' => $operations,
            'special_consultive_clinics' => $special_consultive_clinics,
            'radio_diagnosis' => $radio_diagnosis,
            'maternity_returns' => $maternity_returns,
            'monthly_hospital_statistics' => $monthly_hospital_statistics,
            'immunization_clinic' => $immunization_clinic,
            'family_planning' => $family_planning,
            'communicable_disease' => $communicable_disease,
            'total_facility_attendance' => $total_facility_attendance,
            'health_insurance' => $health_insurance,

            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }
    
    public function SearchMEAggregatedReport(Request $request){
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $operations = '';
        $immunization_report = array();
        $postData = $request->all();
        
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        //  inpatient-records
        $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }

        $patient_general_statistic = array();
        $patientreportData = array();
        $patientreportData_total = array();
        $surgeon_performance_report = array();
        $surgeon_performance_report_total = array();
        $patient_seen = array();
        $patient_seen_temp = array();
        $temp = array();

        // patient general statistic start
            if(isset($postData) && count($postData) > 0 ){
                if($postData['hospitalname'] != ''){
                    $hospitalname1 =$postData['hospitalname'];
                    $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                        if($hospital_name2 != '' && count($hospital_name2) > 0){
                            foreach ($hospital_name2 as $value) {
                                $hospital_name3[]= $value->id;
                            }
                        }
                }
            }
            if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                    $patient_general_statistic = PatientGeneralStatistics::select(DB::raw('SUM(no_of_patient_seen_male) as no_of_patient_seen_male,
                                SUM(no_of_patient_seen_female) as no_of_patient_seen_female,
                                SUM(no_of_delivery_male) as no_of_delivery_male,
                                SUM(no_of_delivery_female) as no_of_delivery_female,
                                SUM(no_of_deaths_male) as no_of_deaths_male,
                                SUM(no_of_deaths_female) as no_of_deaths_female,
                                SUM(no_of_admission_male) as no_of_admission_male,
                                SUM(no_of_admission_female) as no_of_admission_female,
                                SUM(no_of_patient_sc_male) as no_of_patient_sc_male,
                                SUM(no_of_patient_sc_female) as no_of_patient_sc_female,
                                SUM(no_of_discharges_male) as no_of_discharges_male,
                                SUM(no_of_discharges_female) as no_of_discharges_female,
                                SUM(registered_anc_attendees) as registered_anc_attendees,
                                SUM(internally_generated_revenue) as internally_generated_revenue,
                                SUM(registered_anc_attendees_under5m) as registered_anc_attendees_under5m,
                                SUM(registered_anc_attendees_under5f) as registered_anc_attendees_under5f,added_by
                        '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->with('addedBy')->first();
                }else{
                    $patient_general_statistic = PatientGeneralStatistics::select(DB::raw('SUM(no_of_patient_seen_male) as no_of_patient_seen_male,
                                SUM(no_of_patient_seen_female) as no_of_patient_seen_female,
                                SUM(no_of_delivery_male) as no_of_delivery_male,
                                SUM(no_of_delivery_female) as no_of_delivery_female,
                                SUM(no_of_deaths_male) as no_of_deaths_male,
                                SUM(no_of_deaths_female) as no_of_deaths_female,
                                SUM(no_of_admission_male) as no_of_admission_male,
                                SUM(no_of_admission_female) as no_of_admission_female,
                                SUM(no_of_patient_sc_male) as no_of_patient_sc_male,
                                SUM(no_of_patient_sc_female) as no_of_patient_sc_female,
                                SUM(no_of_discharges_male) as no_of_discharges_male,
                                SUM(no_of_discharges_female) as no_of_discharges_female,
                                SUM(registered_anc_attendees) as registered_anc_attendees,
                                SUM(internally_generated_revenue) as internally_generated_revenue,
                                SUM(registered_anc_attendees_under5m) as registered_anc_attendees_under5m,
                                SUM(registered_anc_attendees_under5f) as registered_anc_attendees_under5f,added_by
                        '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                        $q->whereIn('added_by', $hospital_name3);
                                                }
                                                })->first();
                }
            }
            $hospital_display_name = '';
            if($hospitalname1 != ''){
                $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
                $hospital_display_name = $name->hospital_name;
            }
        // patient general statistic end
        
        // patient report data start
            if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                    $patientreportData = PatientSeenReport::groupBy('doctors_name')->select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e, SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental, doctors_name'),"added_by")
                            ->with('addedBy')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->get();



                    $patientreportData_total = PatientSeenReport::select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e,'
                                            . ' SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental'))
                                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            
                            ->first();
                    $patientreportData_total->doctors_name = 'Sum Total';
                    $patientreportData_total->id = '';
                }else{
                    $patientreportData = PatientSeenReport::groupBy('doctors_name')->select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e, SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental, doctors_name'),"added_by")
                            ->with('addedBy')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                        $q->whereIn('added_by', $hospital_name3);
                                                }
                                                })
                            ->get();



                    $patientreportData_total = PatientSeenReport::select(DB::raw('SUM(popd) as popd, SUM(gopd) as gopd, SUM(a_e) as a_e,'
                                            . ' SUM(plastic_surg) as plastic_surg, SUM(urology) as urology , SUM(sopd) as sopd , SUM(o_g) as o_g , SUM(drema) as drema , SUM(mopd) as mopd, SUM(eye) as eye, SUM(ent) as ent, SUM(dental) as dental'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                        $q->whereIn('added_by', $hospital_name3);
                                                }
                                                })
                            ->first();
                }

            }
        // patient report data end

        // surgeon performance report start
            if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                    $surgeon_performance_report = SurgeonPerformanceReport::groupBy('doctors_name')->select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female ,doctors_name'),"added_by")
                            ->with('addedBy')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                            ->get();

                    $surgeon_performance_report_total = SurgeonPerformanceReport::select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                            ->first();

                    $surgeon_performance_report_total->doctors_name = 'Sum Total';
                    $surgeon_performance_report[count($surgeon_performance_report)] = $surgeon_performance_report_total;
                }else{
                    $surgeon_performance_report = SurgeonPerformanceReport::groupBy('doctors_name')->select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female ,doctors_name'),"added_by")
                        ->with('addedBy')
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                                }
                                            })
                        ->get();

                    $surgeon_performance_report_total = SurgeonPerformanceReport::select(DB::raw('SUM(major_operation_male) as major_operation_male, SUM(major_operation_female) as major_operation_female, SUM(intermediate_operation_male) as intermediate_operation_male, SUM(intermediate_operation_female) as intermediate_operation_female, SUM(minor_operation_male) as minor_operation_male , SUM(minor_operation_female) as minor_operation_female'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                            ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                    if($hospitalname1 != ''){
                                                        $q->whereIn('added_by', $hospital_name3);
                                                    }
                                                })
                            ->first();

                    $surgeon_performance_report_total->doctors_name = 'Sum Total';
                    $surgeon_performance_report[count($surgeon_performance_report)] = $surgeon_performance_report_total;
                }
            }
        // surgeon performance report end

        // patient seen start
            if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' ){
                    $patient_seen = PatientSeen::groupBy('doctors_name')->select(DB::raw('SUM(clinical_unit) as clinical_unit,doctors_name'),'added_by')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                            ->with('addedBy')
                            ->get();
                    $patient_seen_temp = PatientSeen::select(DB::raw('SUM(clinical_unit) as clinical_unit'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                            ->first();

                        

                    $patient_seen_temp->doctors_name = 'Sum Total';
                    $patient_seen[count($patient_seen)] = $patient_seen_temp;
                }else{
                    $patient_seen = PatientSeen::groupBy('doctors_name')->select(DB::raw('SUM(clinical_unit) as clinical_unit,doctors_name'),'added_by')
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                                }
                                            })
                            ->with('addedBy')
                            ->get();
                    $patient_seen_temp = PatientSeen::select(DB::raw('SUM(clinical_unit) as clinical_unit'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                                    //->where('year', $postData['year'])

                                ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                                if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                                }
                                            })
                            ->first();
                    

                    $patient_seen_temp->doctors_name = 'Sum Total';
                    $patient_seen[count($patient_seen)] = $patient_seen_temp;
                }
            }
        // patient seen end

        return view('reports.me-aggregated-report',[
            'patient_general_statistic' => $patient_general_statistic,
            'patientreportData' => $patientreportData,
            'surgeon_performance_report' => $surgeon_performance_report,
            'patient_seen' => $patient_seen,
            'totalRow' => $patientreportData_total,

            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'postData' => $postData
        ]);
    }

    public function FamilyPlanningRecord(Request $request){
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $family_planning = '';
        $postData = $request->all();

        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
        }

        if(isset($postData) && count($postData) > 0 ){
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['family_planning'] != '')
            $family_planning = $postData['family_planning']; 
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) { 
                if($hospitalname1 == '' && $family_planning != ''){
                $data = FamilyPlanningRecordOffice::select(DB::raw('SUM(new_family_planning_acceptors_dispensed) as new_family_planning_acceptors_dispensed,
                                                    SUM(new_family_planning_acceptors_used) as new_family_planning_acceptors_used,
                                                    SUM(new_family_planning_acceptors_total) as new_family_planning_acceptors_total,
                                                    SUM(depo_provera_Injection_dispensed) as depo_provera_Injection_dispensed,
                                                    SUM(depo_provera_Injection_used) as depo_provera_Injection_used,
                                                    SUM(depo_provera_Injection_total) as depo_provera_Injection_total,
                                                    SUM(exlution_microlut_dispensed) as exlution_microlut_dispensed,
                                                    SUM(exlution_microlut_used) as exlution_microlut_used,
                                                    SUM(exlution_microlut_total) as exlution_microlut_total,
                                                    SUM(iucd_dispensed) as iucd_dispensed,
                                                    SUM(iucd_used) as iucd_used,
                                                    SUM(iucd_total) as iucd_total,
                                                    SUM(lo_feminal_dispensed) as lo_feminal_dispensed,
                                                    SUM(lo_feminal_used) as lo_feminal_used,
                                                    SUM(lo_feminal_total) as lo_feminal_total,
                                                    SUM(microgynon_dispensed) as microgynon_dispensed,
                                                    SUM(microgynon_used) as microgynon_used,
                                                    SUM(microgynon_total) as microgynon_total,
                                                    SUM(noristerat_dispensed) as noristerat_dispensed,
                                                    SUM(noristerat_used) as noristerat_used,
                                                    SUM(noristerat_total) as noristerat_total,
                                                    SUM(implanon_dispensed) as implanon_dispensed,
                                                    SUM(implanon_used) as implanon_used,
                                                    SUM(implanon_total) as implanon_total,
                                                    SUM(jardelle_dispensed) as jardelle_dispensed,
                                                    SUM(jardelle_used) as jardelle_used,
                                                    SUM(jardelle_total) as jardelle_total,
                                                    SUM(condom_male_and_female_dispensed) as condom_male_and_female_dispensed,
                                                    SUM(condom_male_and_female_used) as condom_male_and_female_used,
                                                    SUM(condom_male_and_female_total) as condom_male_and_female_total,added_by,hospital_id
                                            '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
                    foreach($data as $key => $value1){ 
                        // $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$value1->hospital_id)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                            
                    }
                
                }else{
                    $data = FamilyPlanningRecordOffice::select(DB::raw('SUM(new_family_planning_acceptors_dispensed) as new_family_planning_acceptors_dispensed,
                                                    SUM(new_family_planning_acceptors_used) as new_family_planning_acceptors_used,
                                                    SUM(new_family_planning_acceptors_total) as new_family_planning_acceptors_total,
                                                    SUM(depo_provera_Injection_dispensed) as depo_provera_Injection_dispensed,
                                                    SUM(depo_provera_Injection_used) as depo_provera_Injection_used,
                                                    SUM(depo_provera_Injection_total) as depo_provera_Injection_total,
                                                    SUM(exlution_microlut_dispensed) as exlution_microlut_dispensed,
                                                    SUM(exlution_microlut_used) as exlution_microlut_used,
                                                    SUM(exlution_microlut_total) as exlution_microlut_total,
                                                    SUM(iucd_dispensed) as iucd_dispensed,
                                                    SUM(iucd_used) as iucd_used,
                                                    SUM(iucd_total) as iucd_total,
                                                    SUM(lo_feminal_dispensed) as lo_feminal_dispensed,
                                                    SUM(lo_feminal_used) as lo_feminal_used,
                                                    SUM(lo_feminal_total) as lo_feminal_total,
                                                    SUM(microgynon_dispensed) as microgynon_dispensed,
                                                    SUM(microgynon_used) as microgynon_used,
                                                    SUM(microgynon_total) as microgynon_total,
                                                    SUM(noristerat_dispensed) as noristerat_dispensed,
                                                    SUM(noristerat_used) as noristerat_used,
                                                    SUM(noristerat_total) as noristerat_total,
                                                    SUM(implanon_dispensed) as implanon_dispensed,
                                                    SUM(implanon_used) as implanon_used,
                                                    SUM(implanon_total) as implanon_total,
                                                    SUM(jardelle_dispensed) as jardelle_dispensed,
                                                    SUM(jardelle_used) as jardelle_used,
                                                    SUM(jardelle_total) as jardelle_total,
                                                    SUM(condom_male_and_female_dispensed) as condom_male_and_female_dispensed,
                                                    SUM(condom_male_and_female_used) as condom_male_and_female_used,
                                                    SUM(condom_male_and_female_total) as condom_male_and_female_total
                                                '))
                                                ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                                ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                            if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                            }
                                        })
                            ->first();
                }
            }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
            $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
 
        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $family_planning != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($family_planning == '' || $family_planning == 'new_family_planning_acceptors' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->new_family_planning_acceptors_dispensed, "Used" =>$value->new_family_planning_acceptors_used, "Total" => $value->new_family_planning_acceptors_total);
                        }
                        if($family_planning == '' || $family_planning == 'depo_provera_Injection' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->depo_provera_Injection_dispensed, "Used" =>$value->depo_provera_Injection_used, "Total" => $value->depo_provera_Injection_total);
                        }
                        if($family_planning == '' || $family_planning == 'exlution_microlut' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->exlution_microlut_dispensed, "Used" =>$value->exlution_microlut_used, "Total" => $value->exlution_microlut_total);
                        }
                        if($family_planning == '' || $family_planning == 'iucd' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->iucd_dispensed, "Used" =>$value->iucd_used, "Total" => $value->iucd_total);
                        }
                        if($family_planning == '' || $family_planning == 'lo_feminal' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->lo_feminal_dispensed, "Used" =>$value->lo_feminal_used, "Total" => $value->lo_feminal_total);
                        }
                        if($family_planning == '' || $family_planning == 'microgynon' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->microgynon_dispensed, "Used" =>$value->microgynon_used, "Total" => $value->microgynon_total);
                        }
                        if($family_planning == '' || $family_planning == 'noristerat' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->noristerat_dispensed, "Used" =>$value->noristerat_used, "Total" => $value->noristerat_total);
                        }
                        if($family_planning == '' || $family_planning == 'implanon' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->implanon_dispensed, "Used" =>$value->implanon_used, "Total" => $value->implanon_total);
                        }
                        if($family_planning == '' || $family_planning == 'jardelle' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->jardelle_dispensed, "Used" =>$value->jardelle_used, "Total" => $value->jardelle_total);
                        }
                        if($family_planning == '' || $family_planning == 'condom_male_and_female' ){
                            $chartData[$value->hospital_name] = array("Dispensed" =>$value->condom_male_and_female_dispensed, "Used" =>$value->condom_male_and_female_used, "Total" => $value->condom_male_and_female_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($family_planning == '' || $family_planning == 'new_family_planning_acceptors' ){
                        $chartData['New Family Planning Acceptors'] = array("Dispensed" => $data->new_family_planning_acceptors_dispensed, "Used" => $data->new_family_planning_acceptors_used, "Total" => $data->new_family_planning_acceptors_total);
                    }
                    if($family_planning == '' || $family_planning == 'depo_provera_Injection' ){
                        $chartData['Depo Provera(Injection)'] = array("Dispensed" => $data->depo_provera_Injection_dispensed, "Used" => $data->depo_provera_Injection_used, "Total" => $data->depo_provera_Injection_total);
                    }
                    if($family_planning == '' || $family_planning == 'exlution_microlut' ){
                        $chartData['Exlution/Microlut'] = array("Dispensed" => $data->exlution_microlut_dispensed, "Used" => $data->exlution_microlut_used, "Total" => $data->exlution_microlut_total);
                    }
                    if($family_planning == '' || $family_planning == 'iucd' ){
                        $chartData['IUCD'] = array("Dispensed" => $data->iucd_dispensed, "Used" =>$data->iucd_used, "Total" => $data->iucd_total);
                    }
                    if($family_planning == '' || $family_planning == 'lo_feminal' ){
                        $chartData['Lo-Feminal'] = array("Dispensed" => $data->lo_feminal_dispensed, "Used" => $data->lo_feminal_used, "Total" => $data->lo_feminal_total);
                    }
                    if($family_planning == '' || $family_planning == 'microgynon' ){
                        $chartData['Microgynon'] = array("Dispensed" => $data->microgynon_dispensed, "Used" => $data->microgynon_used, "Total" => $data->microgynon_total);
                    }
                    if($family_planning == '' || $family_planning == 'noristerat' ){
                        $chartData['Noristerat'] = array("Dispensed" => $data->noristerat_dispensed, "Used" => $data->noristerat_used, "Total" => $data->noristerat_total);
                    }
                    if($family_planning == '' || $family_planning == 'implanon' ){
                        $chartData['Implanon'] = array("Dispensed" => $data->implanon_dispensed, "Used" => $data->implanon_used, "Total" => $data->implanon_total);
                    }
                    if($family_planning == '' || $family_planning == 'jardelle' ){
                        $chartData['Jardelle'] = array("Dispensed" => $data->jardelle_dispensed, "Used" => $data->jardelle_used, "Total" => $data->jardelle_total);
                    }
                    if($family_planning == '' || $family_planning == 'condom_male_and_female' ){
                        $chartData['Condom (Male and Female)'] = array("Dispensed" => $data->condom_male_and_female_dispensed, "Used" => $data->condom_male_and_female_used, "Total" => $data->condom_male_and_female_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                } 
        }

        return view('reports.family-planning-record', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'family_planning' => $family_planning,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function CommunicableDisease(Request $request){
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $communicable = '';
        $postData = $request->all();

        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
        }

        if(isset($postData) && count($postData) > 0 ){
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['communicable'] != '')
            $communicable = $postData['communicable']; 
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) { 
                if($hospitalname1 == '' && $communicable != ''){
                    $data = CommunicableDisease::select(DB::raw('SUM(new_malaria_cases_male) as new_malaria_cases_male,SUM(new_malaria_cases_female) as new_malaria_cases_female,SUM(new_malaria_cases_total) as new_malaria_cases_total,SUM(clinic_tested_malaria_male) as clinic_tested_malaria_male,SUM(clinic_tested_malaria_female) as clinic_tested_malaria_female,SUM(clinic_tested_malaria_total) as clinic_tested_malaria_total,SUM(malaria_cases_treated_with_act_male) as malaria_cases_treated_with_act_male,SUM(malaria_cases_treated_with_act_female) as malaria_cases_treated_with_act_female,SUM(malaria_cases_treated_with_act_total) as malaria_cases_treated_with_act_total,SUM(malaria_in_pregnancy_male) as malaria_in_pregnancy_male,SUM(malaria_in_pregnancy_female) as malaria_in_pregnancy_female,SUM(malaria_in_pregnancy_total) as malaria_in_pregnancy_total,SUM(drug_resistance_malaria_cases_male) as drug_resistance_malaria_cases_male,SUM(drug_resistance_malaria_cases_female) as drug_resistance_malaria_cases_female,SUM(drug_resistance_malaria_cases_total) as drug_resistance_malaria_cases_total,SUM(malaria_severe_male) as malaria_severe_male,SUM(malaria_severe_female) as malaria_severe_female,SUM(malaria_severe_total) as malaria_severe_total,SUM(number_of_new_hiv_cases_male) as number_of_new_hiv_cases_male,SUM(number_of_new_hiv_cases_female) as number_of_new_hiv_cases_female,SUM(number_of_new_hiv_cases_total) as number_of_new_hiv_cases_total,SUM(number_of_co_factor_cases_tb_hiv_male) as number_of_co_factor_cases_tb_hiv_male,SUM(number_of_co_factor_cases_tb_hiv_female) as number_of_co_factor_cases_tb_hiv_female,SUM(number_of_co_factor_cases_tb_hiv_total) as number_of_co_factor_cases_tb_hiv_total,SUM(number_of_hiv_persons_on_art_male) as number_of_hiv_persons_on_art_male,SUM(number_of_hiv_persons_on_art_female) as number_of_hiv_persons_on_art_female,SUM(number_of_hiv_persons_on_art_total) as number_of_hiv_persons_on_art_total,SUM(number_of_dropouts_male) as number_of_dropouts_male,SUM(number_of_dropouts_female) as number_of_dropouts_female,SUM(number_of_dropouts_total) as number_of_dropouts_total,SUM(death_complication_related_to_hiv_aids_male) as death_complication_related_to_hiv_aids_male,SUM(death_complication_related_to_hiv_aids_female) as death_complication_related_to_hiv_aids_female,SUM(death_complication_related_to_hiv_aids_total) as death_complication_related_to_hiv_aids_total,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_male) as new_case_of_severe_actute_respiratory_syndrome_sars_male,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_female) as new_case_of_severe_actute_respiratory_syndrome_sars_female,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as new_case_of_severe_actute_respiratory_syndrome_sars_total,SUM(new_case_of_severe_actute_respiratory_illness_sari_male) as new_case_of_severe_actute_respiratory_illness_sari_male,SUM(new_case_of_severe_actute_respiratory_illness_sari_female) as new_case_of_severe_actute_respiratory_illness_sari_female,SUM(new_case_of_severe_actute_respiratory_illness_sari_total) as new_case_of_severe_actute_respiratory_illness_sari_total,SUM(new_case_of_neonatal_tetanus_male) as new_case_of_neonatal_tetanus_male,SUM(new_case_of_neonatal_tetanus_female) as new_case_of_neonatal_tetanus_female,SUM(new_case_of_neonatal_tetanus_total) as new_case_of_neonatal_tetanus_total,SUM(new_case_of_measles_male) as new_case_of_measles_male,SUM(new_case_of_measles_female) as new_case_of_measles_female,SUM(new_case_of_measles_total) as new_case_of_measles_total,SUM(new_case_of_onchcerciasis_male) as new_case_of_onchcerciasis_male,SUM(new_case_of_onchcerciasis_female) as new_case_of_onchcerciasis_female,SUM(new_case_of_onchcerciasis_total) as new_case_of_onchcerciasis_total,SUM(new_case_of_poliomylitis_male) as new_case_of_poliomylitis_male,SUM(new_case_of_poliomylitis_female) as new_case_of_poliomylitis_female,SUM(new_case_of_poliomylitis_total) as new_case_of_poliomylitis_total,SUM(new_case_of_rabies_human_male) as new_case_of_rabies_human_male,SUM(new_case_of_rabies_human_female) as new_case_of_rabies_human_female,SUM(new_case_of_rabies_human_total) as new_case_of_rabies_human_total,SUM(new_case_of_smallpox_male) as new_case_of_smallpox_male,SUM(new_case_of_smallpox_female) as new_case_of_smallpox_female,SUM(new_case_of_smallpox_total) as new_case_of_smallpox_total,SUM(new_case_of_sexually_transmitted_infection_stis_male) as new_case_of_sexually_transmitted_infection_stis_male,SUM(new_case_of_sexually_transmitted_infection_stis_female) as new_case_of_sexually_transmitted_infection_stis_female,SUM(new_case_of_sexually_transmitted_infection_stis_total) as new_case_of_sexually_transmitted_infection_stis_total,SUM(new_case_of_yellow_fever_male) as new_case_of_yellow_fever_male,SUM(new_case_of_yellow_fever_female) as new_case_of_yellow_fever_female,SUM(new_case_of_yellow_fever_total) as new_case_of_yellow_fever_total,SUM(new_case_of_finding_of_tuberculosis_male) as new_case_of_finding_of_tuberculosis_male,SUM(new_case_of_finding_of_tuberculosis_female) as new_case_of_finding_of_tuberculosis_female,SUM(new_case_of_finding_of_tuberculosis_total) as new_case_of_finding_of_tuberculosis_total,SUM(tb_hiv_co_infection_case_male) as tb_hiv_co_infection_case_male,SUM(tb_hiv_co_infection_case_female) as tb_hiv_co_infection_case_female,SUM(tb_hiv_co_infection_case_total) as tb_hiv_co_infection_case_total,SUM(tb_patient_on_art_male) as tb_patient_on_art_male,SUM(tb_patient_on_art_female) as tb_patient_on_art_female,SUM(tb_patient_on_art_total) as tb_patient_on_art_total,SUM(multiple_drug_reaction_tb_cases_male) as multiple_drug_reaction_tb_cases_male,SUM(multiple_drug_reaction_tb_cases_female) as multiple_drug_reaction_tb_cases_female,SUM(multiple_drug_reaction_tb_cases_total) as multiple_drug_reaction_tb_cases_total,SUM(number_of_new_cases_covid_19_male) as number_of_new_cases_covid_19_male,SUM(number_of_new_cases_covid_19_female) as number_of_new_cases_covid_19_female,SUM(number_of_new_cases_covid_19_total) as number_of_new_cases_covid_19_total,SUM(clinic_tested_covid_19_male) as clinic_tested_covid_19_male,SUM(clinic_tested_covid_19_female) as clinic_tested_covid_19_female,SUM(clinic_tested_covid_19_total) as clinic_tested_covid_19_total,SUM(cases_reported_treated_covid_19_male) as cases_reported_treated_covid_19_male,SUM(cases_reported_treated_covid_19_female) as cases_reported_treated_covid_19_female,SUM(cases_reported_treated_covid_19_total) as cases_reported_treated_covid_19_total,SUM(drug_resistenace_covid_19_cases_male) as drug_resistenace_covid_19_cases_male,SUM(drug_resistenace_covid_19_cases_female) as drug_resistenace_covid_19_cases_female,SUM(drug_resistenace_covid_19_cases_total) as drug_resistenace_covid_19_cases_total,SUM(number_of_new_faces_lassa_fever_male) as number_of_new_faces_lassa_fever_male,SUM(number_of_new_faces_lassa_fever_female) as number_of_new_faces_lassa_fever_female,SUM(number_of_new_faces_lassa_fever_total) as number_of_new_faces_lassa_fever_total,SUM(clinic_tested_lassa_fever_male) as clinic_tested_lassa_fever_male,SUM(clinic_tested_lassa_fever_female) as clinic_tested_lassa_fever_female,SUM(clinic_tested_lassa_fever_total) as clinic_tested_lassa_fever_total,SUM(lassa_fever_cases_reported_treated_male) as lassa_fever_cases_reported_treated_male,SUM(lassa_fever_cases_reported_treated_female) as lassa_fever_cases_reported_treated_female,SUM(lassa_fever_cases_reported_treated_total) as lassa_fever_cases_reported_treated_total,SUM(drug_resistance_lassa_fever_cases_male) as drug_resistance_lassa_fever_cases_male,SUM(drug_resistance_lassa_fever_cases_female) as drug_resistance_lassa_fever_cases_female,SUM(drug_resistance_lassa_fever_cases_total) as drug_resistance_lassa_fever_cases_total,SUM(number_of_new_cases_of_cholera_male) as number_of_new_cases_of_cholera_male,SUM(number_of_new_cases_of_cholera_female) as number_of_new_cases_of_cholera_female,SUM(number_of_new_cases_of_cholera_total) as number_of_new_cases_of_cholera_total,added_by,hospital_id'))
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->groupBy('added_by')
                        ->get();
                    foreach($data as $key => $value1){ 
                        // $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$value1->hospital_id)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                            
                    }
                
                }else{
                    $data = CommunicableDisease::select(DB::raw('SUM(new_malaria_cases_male) as new_malaria_cases_male,SUM(new_malaria_cases_female) as new_malaria_cases_female,SUM(new_malaria_cases_total) as new_malaria_cases_total,SUM(clinic_tested_malaria_male) as clinic_tested_malaria_male,SUM(clinic_tested_malaria_female) as clinic_tested_malaria_female,SUM(clinic_tested_malaria_total) as clinic_tested_malaria_total,SUM(malaria_cases_treated_with_act_male) as malaria_cases_treated_with_act_male,SUM(malaria_cases_treated_with_act_female) as malaria_cases_treated_with_act_female,SUM(malaria_cases_treated_with_act_total) as malaria_cases_treated_with_act_total,SUM(malaria_in_pregnancy_male) as malaria_in_pregnancy_male,SUM(malaria_in_pregnancy_female) as malaria_in_pregnancy_female,SUM(malaria_in_pregnancy_total) as malaria_in_pregnancy_total,SUM(drug_resistance_malaria_cases_male) as drug_resistance_malaria_cases_male,SUM(drug_resistance_malaria_cases_female) as drug_resistance_malaria_cases_female,SUM(drug_resistance_malaria_cases_total) as drug_resistance_malaria_cases_total,SUM(malaria_severe_male) as malaria_severe_male,SUM(malaria_severe_female) as malaria_severe_female,SUM(malaria_severe_total) as malaria_severe_total,SUM(number_of_new_hiv_cases_male) as number_of_new_hiv_cases_male,SUM(number_of_new_hiv_cases_female) as number_of_new_hiv_cases_female,SUM(number_of_new_hiv_cases_total) as number_of_new_hiv_cases_total,SUM(number_of_co_factor_cases_tb_hiv_male) as number_of_co_factor_cases_tb_hiv_male,SUM(number_of_co_factor_cases_tb_hiv_female) as number_of_co_factor_cases_tb_hiv_female,SUM(number_of_co_factor_cases_tb_hiv_total) as number_of_co_factor_cases_tb_hiv_total,SUM(number_of_hiv_persons_on_art_male) as number_of_hiv_persons_on_art_male,SUM(number_of_hiv_persons_on_art_female) as number_of_hiv_persons_on_art_female,SUM(number_of_hiv_persons_on_art_total) as number_of_hiv_persons_on_art_total,SUM(number_of_dropouts_male) as number_of_dropouts_male,SUM(number_of_dropouts_female) as number_of_dropouts_female,SUM(number_of_dropouts_total) as number_of_dropouts_total,SUM(death_complication_related_to_hiv_aids_male) as death_complication_related_to_hiv_aids_male,SUM(death_complication_related_to_hiv_aids_female) as death_complication_related_to_hiv_aids_female,SUM(death_complication_related_to_hiv_aids_total) as death_complication_related_to_hiv_aids_total,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_male) as new_case_of_severe_actute_respiratory_syndrome_sars_male,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_female) as new_case_of_severe_actute_respiratory_syndrome_sars_female,SUM(new_case_of_severe_actute_respiratory_syndrome_sars_total) as new_case_of_severe_actute_respiratory_syndrome_sars_total,SUM(new_case_of_severe_actute_respiratory_illness_sari_male) as new_case_of_severe_actute_respiratory_illness_sari_male,SUM(new_case_of_severe_actute_respiratory_illness_sari_female) as new_case_of_severe_actute_respiratory_illness_sari_female,SUM(new_case_of_severe_actute_respiratory_illness_sari_total) as new_case_of_severe_actute_respiratory_illness_sari_total,SUM(new_case_of_neonatal_tetanus_male) as new_case_of_neonatal_tetanus_male,SUM(new_case_of_neonatal_tetanus_female) as new_case_of_neonatal_tetanus_female,SUM(new_case_of_neonatal_tetanus_total) as new_case_of_neonatal_tetanus_total,SUM(new_case_of_measles_male) as new_case_of_measles_male,SUM(new_case_of_measles_female) as new_case_of_measles_female,SUM(new_case_of_measles_total) as new_case_of_measles_total,SUM(new_case_of_onchcerciasis_male) as new_case_of_onchcerciasis_male,SUM(new_case_of_onchcerciasis_female) as new_case_of_onchcerciasis_female,SUM(new_case_of_onchcerciasis_total) as new_case_of_onchcerciasis_total,SUM(new_case_of_poliomylitis_male) as new_case_of_poliomylitis_male,SUM(new_case_of_poliomylitis_female) as new_case_of_poliomylitis_female,SUM(new_case_of_poliomylitis_total) as new_case_of_poliomylitis_total,SUM(new_case_of_rabies_human_male) as new_case_of_rabies_human_male,SUM(new_case_of_rabies_human_female) as new_case_of_rabies_human_female,SUM(new_case_of_rabies_human_total) as new_case_of_rabies_human_total,SUM(new_case_of_smallpox_male) as new_case_of_smallpox_male,SUM(new_case_of_smallpox_female) as new_case_of_smallpox_female,SUM(new_case_of_smallpox_total) as new_case_of_smallpox_total,SUM(new_case_of_sexually_transmitted_infection_stis_male) as new_case_of_sexually_transmitted_infection_stis_male,SUM(new_case_of_sexually_transmitted_infection_stis_female) as new_case_of_sexually_transmitted_infection_stis_female,SUM(new_case_of_sexually_transmitted_infection_stis_total) as new_case_of_sexually_transmitted_infection_stis_total,SUM(new_case_of_yellow_fever_male) as new_case_of_yellow_fever_male,SUM(new_case_of_yellow_fever_female) as new_case_of_yellow_fever_female,SUM(new_case_of_yellow_fever_total) as new_case_of_yellow_fever_total,SUM(new_case_of_finding_of_tuberculosis_male) as new_case_of_finding_of_tuberculosis_male,SUM(new_case_of_finding_of_tuberculosis_female) as new_case_of_finding_of_tuberculosis_female,SUM(new_case_of_finding_of_tuberculosis_total) as new_case_of_finding_of_tuberculosis_total,SUM(tb_hiv_co_infection_case_male) as tb_hiv_co_infection_case_male,SUM(tb_hiv_co_infection_case_female) as tb_hiv_co_infection_case_female,SUM(tb_hiv_co_infection_case_total) as tb_hiv_co_infection_case_total,SUM(tb_patient_on_art_male) as tb_patient_on_art_male,SUM(tb_patient_on_art_female) as tb_patient_on_art_female,SUM(tb_patient_on_art_total) as tb_patient_on_art_total,SUM(multiple_drug_reaction_tb_cases_male) as multiple_drug_reaction_tb_cases_male,SUM(multiple_drug_reaction_tb_cases_female) as multiple_drug_reaction_tb_cases_female,SUM(multiple_drug_reaction_tb_cases_total) as multiple_drug_reaction_tb_cases_total,SUM(number_of_new_cases_covid_19_male) as number_of_new_cases_covid_19_male,SUM(number_of_new_cases_covid_19_female) as number_of_new_cases_covid_19_female,SUM(number_of_new_cases_covid_19_total) as number_of_new_cases_covid_19_total,SUM(clinic_tested_covid_19_male) as clinic_tested_covid_19_male,SUM(clinic_tested_covid_19_female) as clinic_tested_covid_19_female,SUM(clinic_tested_covid_19_total) as clinic_tested_covid_19_total,SUM(cases_reported_treated_covid_19_male) as cases_reported_treated_covid_19_male,SUM(cases_reported_treated_covid_19_female) as cases_reported_treated_covid_19_female,SUM(cases_reported_treated_covid_19_total) as cases_reported_treated_covid_19_total,SUM(drug_resistenace_covid_19_cases_male) as drug_resistenace_covid_19_cases_male,SUM(drug_resistenace_covid_19_cases_female) as drug_resistenace_covid_19_cases_female,SUM(drug_resistenace_covid_19_cases_total) as drug_resistenace_covid_19_cases_total,SUM(number_of_new_faces_lassa_fever_male) as number_of_new_faces_lassa_fever_male,SUM(number_of_new_faces_lassa_fever_female) as number_of_new_faces_lassa_fever_female,SUM(number_of_new_faces_lassa_fever_total) as number_of_new_faces_lassa_fever_total,SUM(clinic_tested_lassa_fever_male) as clinic_tested_lassa_fever_male,SUM(clinic_tested_lassa_fever_female) as clinic_tested_lassa_fever_female,SUM(clinic_tested_lassa_fever_total) as clinic_tested_lassa_fever_total,SUM(lassa_fever_cases_reported_treated_male) as lassa_fever_cases_reported_treated_male,SUM(lassa_fever_cases_reported_treated_female) as lassa_fever_cases_reported_treated_female,SUM(lassa_fever_cases_reported_treated_total) as lassa_fever_cases_reported_treated_total,SUM(drug_resistance_lassa_fever_cases_male) as drug_resistance_lassa_fever_cases_male,SUM(drug_resistance_lassa_fever_cases_female) as drug_resistance_lassa_fever_cases_female,SUM(drug_resistance_lassa_fever_cases_total) as drug_resistance_lassa_fever_cases_total,SUM(number_of_new_cases_of_cholera_male) as number_of_new_cases_of_cholera_male,SUM(number_of_new_cases_of_cholera_female) as number_of_new_cases_of_cholera_female,SUM(number_of_new_cases_of_cholera_total) as number_of_new_cases_of_cholera_total'))
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                            if($hospitalname1 != ''){
                                    $q->whereIn('added_by', $hospital_name3);
                            }
                        })
                        ->first();
                }
            }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
            $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
 
        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();

        // if(isset($data) && count($data) > 0){
            
        //         if($hospitalname1 == "" && $family_planning != ""){
        //             $chartData = array();
        //             foreach($data as $key => $value){
        //                 if($family_planning == '' || $family_planning == 'depo_provera_Injection' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->depo_provera_Injection_dispensed, "Used" =>$value->depo_provera_Injection_used, "Total" => $value->depo_provera_Injection_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'exlution_microlut' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->exlution_microlut_dispensed, "Used" =>$value->exlution_microlut_used, "Total" => $value->exlution_microlut_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'iucd' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->iucd_dispensed, "Used" =>$value->iucd_used, "Total" => $value->iucd_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'lo_feminal' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->lo_feminal_dispensed, "Used" =>$value->lo_feminal_used, "Total" => $value->lo_feminal_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'microgynon' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->microgynon_dispensed, "Used" =>$value->microgynon_used, "Total" => $value->microgynon_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'noristerat' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->noristerat_dispensed, "Used" =>$value->noristerat_used, "Total" => $value->noristerat_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'implanon' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->implanon_dispensed, "Used" =>$value->implanon_used, "Total" => $value->implanon_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'jardelle' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->jardelle_dispensed, "Used" =>$value->jardelle_used, "Total" => $value->jardelle_total);
        //                 }
        //                 if($family_planning == '' || $family_planning == 'condom_male_and_female' ){
        //                     $chartData[$value->hospital_name] = array("Dispensed" =>$value->condom_male_and_female_dispensed, "Used" =>$value->condom_male_and_female_used, "Total" => $value->condom_male_and_female_total);
        //                 }
        //             }
        //         }else{
        //             $chartData = array();
        //             if($family_planning == '' || $family_planning == 'depo_provera_Injection' ){
        //                 $chartData['Depo Provera(Injection)'] = array("Dispensed" => $data->depo_provera_Injection_dispensed, "Used" => $data->depo_provera_Injection_used, "Total" => $data->depo_provera_Injection_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'exlution_microlut' ){
        //                 $chartData['Exlution/Microlut'] = array("Dispensed" => $data->exlution_microlut_dispensed, "Used" => $data->exlution_microlut_used, "Total" => $data->exlution_microlut_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'iucd' ){
        //                 $chartData['IUCD'] = array("Dispensed" => $data->iucd_dispensed, "Used" =>$data->iucd_used, "Total" => $data->iucd_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'lo_feminal' ){
        //                 $chartData['Lo-Feminal'] = array("Dispensed" => $data->lo_feminal_dispensed, "Used" => $data->lo_feminal_used, "Total" => $data->lo_feminal_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'microgynon' ){
        //                 $chartData['Microgynon'] = array("Dispensed" => $data->microgynon_dispensed, "Used" => $data->microgynon_used, "Total" => $data->microgynon_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'noristerat' ){
        //                 $chartData['Noristerat'] = array("Dispensed" => $data->noristerat_dispensed, "Used" => $data->noristerat_used, "Total" => $data->noristerat_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'implanon' ){
        //                 $chartData['Implanon'] = array("Dispensed" => $data->implanon_dispensed, "Used" => $data->implanon_used, "Total" => $data->implanon_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'jardelle' ){
        //                 $chartData['Jardelle'] = array("Dispensed" => $data->jardelle_dispensed, "Used" => $data->jardelle_used, "Total" => $data->jardelle_total);
        //             }
        //             if($family_planning == '' || $family_planning == 'condom_male_and_female' ){
        //                 $chartData['Condom (Male and Female)'] = array("Dispensed" => $data->condom_male_and_female_dispensed, "Used" => $data->condom_male_and_female_used, "Total" => $data->condom_male_and_female_total);
        //             }
        //         }

        //         $labelArray = array_keys($chartData);
        //         $chartFinalData = array();
        //         $piechartData = array();
        //         $pieChartColor  = array();
        //         foreach($chartData as $key => $chart){
        //             $k = 0;
        //             foreach($chart as $index => $value){
        //                 if($index == "Total"){
        //                     $piechartData[$k][$key." ".$index] = $value;
        //                     $pieChartColor[] = $this->rndRGBColorCode();
        //                 }
        //                 if($index != "Total"){
        //                     if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
        //                         $chartFinalData[$k]['data'][] = $value;
        //                     }else{
        //                         $chartFinalData[$k]['label'] = $index;
        //                         $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
        //                         if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
        //                             $chartFinalData[$k]['data'][] = $value;
        //                         }else{
        //                             $chartFinalData[$k]['data'][] = $value;
        //                         }
        //                     }
        //                 }
        //                 $k++;   
        //             }
        //         }
        //         if(!empty($piechartData)){
        //             $piechartData = array_values($piechartData);
        //             $piechartData = $piechartData[0]; 
        //         }else{
        //             $piechartData = array();
        //         } 
        // }

        return view('reports.communicable-disease', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'communicable' => $communicable,
            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function TotalFacilityAttendance(Request $request){
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $total_facility = '';
        $postData = $request->all();

        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
        }

        if(isset($postData) && count($postData) > 0 ){
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['total_facility'] != '')
            $total_facility = $postData['total_facility']; 
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) { 
                if($hospitalname1 == '' && $total_facility != ''){
                $data = TotalFacilityAttendance::select(DB::raw('SUM(total_facility_attendance_male) as total_facility_attendance_male,
                                                    SUM(total_facility_attendance_female) as total_facility_attendance_female,
                                                    SUM(total_facility_attendance_total) as total_facility_attendance_total,added_by,hospital_id
                                            '))
                    ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                    ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                    ->groupBy('added_by')
                    ->get();
                    foreach($data as $key => $value1){ 
                        // $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$value1->hospital_id)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;
                            
                    }
                
                }else{
                    $data = TotalFacilityAttendance::select(DB::raw('SUM(total_facility_attendance_male) as total_facility_attendance_male,
                                                        SUM(total_facility_attendance_female) as total_facility_attendance_female,
                                                        SUM(total_facility_attendance_total) as total_facility_attendance_total
                                                    '))
                                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                            if($hospitalname1 != ''){
                                                    $q->whereIn('added_by', $hospital_name3);
                                            }
                                        })
                            ->first();
                }
            }
        $hospital_display_name = '';
        if($hospitalname1 != ''){
            $name= Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }
 
        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $total_facility != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($total_facility == '' || $total_facility == 'total_facility_attendance' ){
                            $chartData[$value->hospital_name] = array("Male" =>$value->total_facility_attendance_male, "Female" =>$value->total_facility_attendance_female, "Total" => $value->total_facility_attendance_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($total_facility == '' || $total_facility == 'total_facility_attendance' ){
                        $chartData['Total Facility Attendance'] = array("Male" => $data->total_facility_attendance_male, "Female" => $data->total_facility_attendance_female, "Total" => $data->total_facility_attendance_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechart = array();
                    $piechartData = array_values($piechartData);
                    foreach($piechartData as $key => $value){
                        foreach($value as $k => $v){
                            $piechart[$k] = $v;
                        }
                    }
                    $piechartData = $piechart;
                }else{
                    $piechartData = array();
                } 

        }

        return view('reports.total-facility-attendance', [
            'data' => $data,
            'years' => $years,
            'hospital_display_name'=>$hospital_display_name,
            'hospital_name' => $hospital_name,
            'hospitalname' => $hospitalname1,
            'total_facility' => $total_facility,

            'postData' => $postData,
            'labelArray' => $labelArray,
            'chartFinalData' => $chartFinalData,
            'piechartData' => $piechartData,
            'pieChartColor' => $pieChartColor
        ]);
    }

    public function HealthInsurance(Request $request) {
        $current_year = date('Y');
        $current_month = date('m');
        $years = range($current_year - 5, $current_year + 10);
        $hospital_name = array();
        $hospital_name3 = array();
        $hospital_name = Hospitals::get();

        $request->flash();
        $hospitalname = '';
        $hospitalname1 = '';
        $health_insurance = '';
        $postData = $request->all();
        if(isset($postData) && count($postData) > 0 ){
            $postData['month_from'] = str_replace('/','-',$postData['month_from']);
            $postData['month_to'] = str_replace('/','-',$postData['month_to']);
            if($postData['hospitalname'] != ''){
                
                $hospitalname1= '';
                $hospitalname1 =$postData['hospitalname'];
                $hospital_name2 = User::where('hospital_name',$hospitalname1)->get();
                    if($hospital_name2 != '' && count($hospital_name2) > 0){
                        foreach ($hospital_name2 as $value) {
                            $hospital_name3[]= $value->id;
                        }
                    }
            }
            
        }
        
        if(isset($postData) && count($postData) > 0 ){
            if($postData['health_insurance'] != ''){
                $health_insurance = $postData['health_insurance'];
            }
        }
        
        $data = array();

            if (isset($postData) && count($postData) > 0) {
                if($hospitalname1 == '' && $health_insurance){
                   $data = HealthInsurance::
                           select(DB::raw('SUM(nhis_male) as nhis_male,
                                            SUM(nhis_female) as nhis_female,
                                            SUM(nhis_total) as nhis_total,
                                            SUM(fhis_male) as fhis_male,
                                            SUM(fhis_female) as fhis_female,
                                            SUM(fhis_total) as fhis_total,
                                            SUM(nhis_enrolled_male) as nhis_enrolled_male,
                                            SUM(nhis_enrolled_female) as nhis_enrolled_female,
                                            SUM(nhis_enrolled_total) as nhis_enrolled_total,
                                            SUM(fhis_enrolled_male) as fhis_enrolled_male,
                                            SUM(fhis_enrolled_female) as fhis_enrolled_female,
                                            SUM(fhis_enrolled_total) as fhis_enrolled_total,added_by,hospital_id'))
                            ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                            ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                            ->groupBy('added_by')
                            ->get();
                  
                    foreach($data as $key => $value1){ 
                        $name = User::where('id',$value1->added_by)->first();
                        $hospital_name1 = Hospitals::where('id',$name->hospital_name)->first();
                        $data[$key]['hospital_name'] = $hospital_name1->hospital_name;   
                    }
                   
                }else{
                    $data = HealthInsurance::select(DB::raw('SUM(nhis_male) as nhis_male,
                                            SUM(nhis_female) as nhis_female,
                                            SUM(nhis_total) as nhis_total,
                                            SUM(fhis_male) as fhis_male,
                                            SUM(fhis_female) as fhis_female,
                                            SUM(fhis_total) as fhis_total,
                                            SUM(nhis_enrolled_male) as nhis_enrolled_male,
                                            SUM(nhis_enrolled_female) as nhis_enrolled_female,
                                            SUM(nhis_enrolled_total) as nhis_enrolled_total,
                                            SUM(fhis_enrolled_male) as fhis_enrolled_male,
                                            SUM(fhis_enrolled_female) as fhis_enrolled_female,
                                            SUM(fhis_enrolled_total) as fhis_enrolled_total'))
                        ->where('created_at','>=', date('Y-m-d',strtotime($postData['month_from'])))
                        ->where('created_at','<=', date('Y-m-d',strtotime($postData['month_to'])))
                        // ->where('hospital_id', $hospitalname1)
                        ->where(function ($q) use ($hospital_name3,$hospitalname1) {
                                       if($hospitalname1 != ''){
                                            $q->whereIn('added_by', $hospital_name3);
                                       }
                                    })
                        ->first();
                }
           }
             $hospital_display_name = '';
        if($hospitalname1 != ''){
             $name = Hospitals::select(DB::raw('hospital_name'))->where('id',$hospitalname1)->first();
            $hospital_display_name = $name->hospital_name;
        }


        $chartFinalData = array();
        $labelArray = array();
        $piechartData = array();
        $pieChartColor  = array();
        if(isset($data) && count($data) > 0){
            
                if($hospitalname1 == "" && $health_insurance != ""){
                    $chartData = array();
                    foreach($data as $key => $value){
                        if($health_insurance == 'national_health_insurance' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->nhis_male, "Female" => $value->nhis_female, "Total" => $value->nhis_total);
                        }
                        if($health_insurance == 'fct_health_insurance' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->fhis_male, "Female" => $value->fhis_female, "Total" => $value->fhis_total);
                        }
                        if($health_insurance == 'nhis_enrolled' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->nhis_enrolled_male, "Female" => $value->nhis_enrolled_female, "Total" => $value->nhis_enrolled_total);
                        }
                        if($health_insurance == 'fhis_enrolled' ){
                            $chartData[$value->hospital_name] = array("Male" => $value->fhis_enrolled_male, "Female" => $value->fhis_enrolled_female, "Total" => $value->fhis_enrolled_total);
                        }
                    }
                }else{
                    $chartData = array();
                    if($health_insurance == '' || $health_insurance == 'national_health_insurance' ){
                        $chartData['National Health Insurance(NHIS)'] = array("Male" => $data->nhis_male, "Female" => $data->nhis_female, "Total" => $data->nhis_total);
                    }
                    if($health_insurance == '' || $health_insurance == 'fct_health_insurance' ){
                        $chartData['FCT Health Insurance(FHIS)'] = array("Male" => $data->fhis_male, "Female" => $data->fhis_female, "Total" => $data->fhis_total);
                    }
                    if($health_insurance == '' || $health_insurance == 'nhis_enrolled' ){
                        $chartData['Number of NHIS Enrolled'] = array("Male" => $data->nhis_enrolled_male, "Female" =>$data->nhis_enrolled_female, "Total" => $data->nhis_enrolled_total);
                    }
                    if($health_insurance == '' || $health_insurance == 'fhis_enrolled' ){
                        $chartData['Number of FHIS Enrolled'] = array("Male" => $data->fhis_enrolled_male, "Female" =>$data->fhis_enrolled_female, "Total" => $data->fhis_enrolled_total);
                    }
                }

                $labelArray = array_keys($chartData);
                $chartFinalData = array();
                $piechartData = array();
                $pieChartColor  = array();
                foreach($chartData as $key => $chart){
                    $k = 0;
                    foreach($chart as $index => $value){
                        if($index == "Total"){
                            $piechartData[$k][$key." ".$index] = $value;
                            $pieChartColor[] = $this->rndRGBColorCode();
                        }
                        if($index != "Total"){
                            if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                $chartFinalData[$k]['data'][] = $value;
                            }else{
                                $chartFinalData[$k]['label'] = $index;
                                $chartFinalData[$k]['backgroundColor'] = $this->rndRGBColorCode();
                                if(isset($chartFinalData[$k]['label']) && $chartFinalData[$k]['label'] == $index){
                                    $chartFinalData[$k]['data'][] = $value;
                                }else{
                                    $chartFinalData[$k]['data'][] = $value;
                                }
                            }
                        }
                        $k++;   
                    }
                }
                if(!empty($piechartData)){
                    $piechartData = array_values($piechartData);
                    $piechartData = $piechartData[0]; 
                }else{
                    $piechartData = array();
                }  
        }


           return view('reports.health-insurance', [
                        'data' => $data,
                        'years' => $years,
                        'month_from' => '',
                        'hospital_display_name'=>$hospital_display_name,
                        'hospital_name' => $hospital_name,
                        'hospitalname' => $hospitalname1,
                        'health_insurance' => $health_insurance,
                        'postData' => $postData,
                        'labelArray' => $labelArray,
                        'chartFinalData' => $chartFinalData,
                        'piechartData' => $piechartData,
                        'pieChartColor' => $pieChartColor            
                    ]);
    }

}