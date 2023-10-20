<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Validator;
use DB;
use Auth;
use App\User;
use App\Hospitals;
use App\InpatientRecords;
use App\AccidentEmergency;
use App\GeneralOutpatient;
use App\LaboratoryInvestigations;
use App\RadioDiagnosis;
use App\Operations;
use App\SpecialConsultiveClinics;
use App\MaternityReturns;
use App\ImmunizationClinic;
use App\MonthlyHospitalStatistics;
use App\DoctorPerformance;
use App\PatientSeenReport;
use App\Immunization;
use App\PatientSeen;
use App\CronHistory;
use App\Notification;
use App\TotalFacilityAttendance;
use App\PatientGeneralStatistics;
use App\DeleteNotification;
use App\UserMessages;
use App\FamilyPlanningRecordOffice;
use App\HealthInsurance;
use App\CommunicableDisease;
use App\DiagnosisCount;

use App\HistoryApiCall;
use App\DemoEntryTable;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    public function get_report_new(Request $request)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errrors', 1);
        ini_set('memory_limit','-1');


        $data = json_decode($request[0]);
        $date = date('Y-m-d');

        // echo "<pre>"; print_r($data); exit;

        Log::info($data->created_at);


        $delete_noti = DeleteNotification::where('reason','delete notification')->orderby('id','desc')->first();
        if(!empty($delete_noti) && $delete_noti != ''){
            $newdateinfo =  date("Y-m-d",strtotime($delete_noti->new_delete_date));
            if($newdateinfo == $date){
                // echo 1; exit;
                $new_date = strtotime('+3 day', strtotime($date));
                $new_date = date("Y-m-d",$new_date);
                $new_date_delete = strtotime('-3 day', strtotime($date));
                $new_date_delete = date("Y-m-d 23:59:59",$new_date_delete);

                $notification = Notification::where('created_at','<=',$new_date_delete)->delete();

                $message = UserMessages::where('created_at','<=',$new_date_delete)->delete();

                $new_delete = new DeleteNotification();
                $new_delete->reason = 'delete notification';
                $new_delete->last_delete_date = $date;
                $new_delete->new_delete_date = $new_date;
                $new_delete->save();
            }
        }

        if(isset($data->hospital_registration)){
            $reg = $data->hospital_registration;
            $hospital_exist = DB::table('hospitals')
                ->where('hospitals.registration_number', $reg)
                ->first();

            $h_id = $hospital_exist->id;
        }

        if(empty($hospital_exist)){
            if(isset($data->hospital)){
                $hospitals = new Hospitals();
                $hospitals->added_by = 11;
                foreach($data->hospital as $column => $value){
                    $hospitals->$column = $value;
                }
                $hospitals->save();
                $h_id = $hospitals->id;
                $h_name = $hospitals->hospital_name;
            }
        }else{
            $h_id = $hospital_exist->id;
            $h_name = $hospital_exist->hospital_name;
            $hospital = Hospitals::find($h_id);
            if($hospital_exist->category == 'Primary Healthcare Centre' || $hospital_exist->category == 1){
                $hospital->category = 1;
            }else if($hospital_exist->category == 'Secondary Healthcare Centre' || $hospital_exist->category == 2){
                $hospital->category = 2;
            }else if($hospital_exist->category == 'tertiary Healthcare Centre' || $hospital_exist->category == 3){
                $hospital->category = 3;
            }else{
                $hospital->category = 2;
            }
            $hospital->save();
        }

        // $h_id = 7;

        if(isset($data->inpatient_record)){
            $inpatient_records = InpatientRecords::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($inpatient_records)){
                $inpatient = new InpatientRecords();
                $inpatient->added_by = 11;
                $inpatient->month = date("m");
                $inpatient->year = date("Y");
                $inpatient->hospital_id = $h_id;
                $inpatient->created_at = $data->created_at;
                foreach($data->inpatient_record as $column => $value){
                    $inpatient->$column = $value == '0' ? 0 : $value;
                }
                $inpatient->save();
            }else{
                $inpatient = InpatientRecords::find($inpatient_records->id);
                $inpatient->added_by = 11;  
                $inpatient->month = date("m");
                $inpatient->year = date("Y");
                $inpatient->hospital_id = $h_id;
                $inpatient->created_at = $data->created_at;
                foreach($data->inpatient_record as $column => $value){
                    $inpatient->$column = $value == '0' ? 0 : $value;
                }
                $inpatient->save();
            }

            
        }

        if(isset($data->accident_emergency)){
            $accident_emergencies = AccidentEmergency::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($accident_emergencies)){
                $accident = new AccidentEmergency();
                $accident->added_by = 11;
                $accident->month = date("m");
                $accident->year = date("Y");
                $accident->hospital_id = $h_id;
                $accident->created_at = $data->created_at;
                foreach($data->accident_emergency as $column => $value){
                    $accident->$column = $value == '0' ? 0 : $value;
                }
                $accident->save();
            }else{
                $accident = AccidentEmergency::find($accident_emergencies->id);
                $accident->added_by = 11;
                $accident->month = date("m");
                $accident->year = date("Y");
                $accident->hospital_id = $h_id;
                $accident->created_at = $data->created_at;
                foreach($data->accident_emergency as $column => $value){
                    $accident->$column = $value == '0' ? 0 : $value;
                }
                $accident->save();
            }
        }

        if(isset($data->general_outpatient)){
            $general_outpatients = GeneralOutpatient::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($general_outpatients)){
                $general = new GeneralOutpatient();
                $general->added_by = 11;
                $general->month = date("m");
                $general->year = date("Y");
                $general->hospital_id = $h_id;
                $general->created_at = $data->created_at;
                foreach($data->general_outpatient as $column => $value){
                    $general->$column = $value == '0' ? 0 : $value;
                }
                $general->save();
            }else{
                $general = GeneralOutpatient::find($general_outpatients->id);
                $general->added_by = 11;
                $general->month = date("m");
                $general->year = date("Y");
                $general->hospital_id = $h_id;
                $general->created_at = $data->created_at;
                foreach($data->general_outpatient as $column => $value){
                    $general->$column = $value == '0' ? 0 : $value;
                }
                $general->save();
            }
        }

        if(isset($data->laboratory)){
            $laboratorys = LaboratoryInvestigations::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($laboratorys)){
                $laboratories = new LaboratoryInvestigations();
                $laboratories->added_by = 11;
                $laboratories->month = date("m");
                $laboratories->year = date("Y");
                $laboratories->hospital_id = $h_id;
                $laboratories->created_at = $data->created_at;
                foreach($data->laboratory as $column => $value){
                    $laboratories->$column = $value == '0' ? 0 : $value;
                }
                $laboratories->save();
            }else{
                $laboratories = LaboratoryInvestigations::find($laboratorys->id);
                $laboratories->added_by = 11;
                $laboratories->month = date("m");
                $laboratories->year = date("Y");
                $laboratories->hospital_id = $h_id;
                $laboratories->created_at = $data->created_at;
                foreach($data->laboratory as $column => $value){
                    $laboratories->$column = $value == '0' ? 0 : $value;
                }
                $laboratories->save();
            }
            
        }
        
        if(isset($data->oparetion)){
            $opare = Operations::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($opare)){
                $oparetions = new Operations();
                $oparetions->added_by = 11;
                $oparetions->month = date("m");
                $oparetions->year = date("Y");
                $oparetions->hospital_id = $h_id;
                $oparetions->created_at = $data->created_at;
                foreach($data->oparetion as $column => $value){
                    $oparetions->$column = $value == '0' ? 0 : $value;
                }
                $oparetions->save();
            }else{
                $oparetions = Operations::find($opare->id);
                $oparetions->added_by = 11;
                $oparetions->month = date("m");
                $oparetions->year = date("Y");
                $oparetions->hospital_id = $h_id;
                $oparetions->created_at = $data->created_at;
                foreach($data->oparetion as $column => $value){
                    $oparetions->$column = $value == '0' ? 0 : $value;
                }
                $oparetions->save();
            }
        }
        
        if(isset($data->special_consultive)){
            $special = SpecialConsultiveClinics::where('created_at','LIKE','%'.$data->created_at.'%')->first();
            
            if(empty($special)){
                $special_consultives = new SpecialConsultiveClinics();
                $special_consultives->added_by = 11;
                $special_consultives->month = date("m");
                $special_consultives->year = date("Y");
                $special_consultives->hospital_id = $h_id;
                $special_consultives->created_at = $data->created_at;
                foreach($data->special_consultive as $column => $value){
                    $special_consultives->$column = $value == '0' ? 0 : $value;
                }
                $special_consultives->save();
            }else{
                $special_consultives = SpecialConsultiveClinics::find($special->id);
                $special_consultives->added_by = 11;
                $special_consultives->month = date("m");
                $special_consultives->year = date("Y");
                $special_consultives->hospital_id = $h_id;
                $special_consultives->created_at = $data->created_at;
                foreach($data->special_consultive as $column => $value){
                    $special_consultives->$column = $value == '0' ? 0 : $value;
                }
                $special_consultives->save();
            }
        }
        
        if(isset($data->radiology)){
            $radiologies = RadioDiagnosis::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($radiologies)){
                $radio = new RadioDiagnosis();
                $radio->added_by = 11;
                $radio->month = date("m");
                $radio->year = date("Y");
                $radio->hospital_id = $h_id;
                $radio->created_at = $data->created_at;
                foreach($data->radiology as $column => $value){
                    $radio->$column = $value == '0' ? 0 : $value;
                }
                $radio->save();
            }else{
                $radio = RadioDiagnosis::find($radiologies->id);
                $radio->added_by = 11;
                $radio->month = date("m");
                $radio->year = date("Y");
                $radio->hospital_id = $h_id;
                $radio->created_at = $data->created_at;
                foreach($data->radiology as $column => $value){
                    $radio->$column = $value == '0' ? 0 : $value;
                }
                $radio->save();
            }
        }
        
        if(isset($data->maternity_returns)){
            $maternity = MaternityReturns::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($maternity)){
                $maternity_return = new MaternityReturns();
                $maternity_return->added_by = 11;
                $maternity_return->month = date("m");
                $maternity_return->year = date("Y");
                $maternity_return->hospital_id = $h_id;
                $maternity_return->created_at = $data->created_at;
                foreach($data->maternity_returns as $column => $value){
                    $maternity_return->$column = $value == '0' ? 0 : $value;
                }
                $maternity_return->save();
            }else{
                $maternity_return = MaternityReturns::find($maternity->id);
                $maternity_return->added_by = 11;
                $maternity_return->month = date("m");
                $maternity_return->year = date("Y");
                $maternity_return->hospital_id = $h_id;
                $maternity_return->created_at = $data->created_at;
                foreach($data->maternity_returns as $column => $value){
                    $maternity_return->$column = $value == '0' ? 0 : $value;
                }
                $maternity_return->save();
            }
        }

        if(isset($data->immunization_clinic)){
            $immuniza = ImmunizationClinic::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($immuniza)){
                $immunization = new ImmunizationClinic();
                $immunization->added_by = 11;
                $immunization->month = date("m");
                $immunization->year = date("Y");
                $immunization->hospital_id = $h_id;
                $immunization->created_at = $data->created_at;
                foreach($data->immunization_clinic as $column => $value){
                    $immunization->$column = $value == '0' ? 0 : $value;
                }
                $immunization->save();
            }else{
                $immunization = ImmunizationClinic::find($immuniza->id);
                $immunization->added_by = 11;
                $immunization->month = date("m");
                $immunization->year = date("Y");
                $immunization->hospital_id = $h_id;
                $immunization->created_at = $data->created_at;
                foreach($data->immunization_clinic as $column => $value){
                    $immunization->$column = $value == '0' ? 0 : $value;
                }
                $immunization->save();
            }
        }

        if(isset($data->monthly_statis)){
            $monthly = MonthlyHospitalStatistics::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($monthly)){
                $monthly_sta = new MonthlyHospitalStatistics();
                $monthly_sta->added_by = 11;
                $monthly_sta->month = date("m");
                $monthly_sta->year = date("Y");
                $monthly_sta->hospital_id = $h_id;
                $monthly_sta->created_at = $data->created_at;
                foreach($data->monthly_statis as $column => $value){
                    $monthly_sta->$column = $value == '0' ? 0 : $value;
                }
                $monthly_sta->save();
            }else{
                $monthly_sta = MonthlyHospitalStatistics::find($monthly->id);
                $monthly_sta->added_by = 11;
                $monthly_sta->month = date("m");
                $monthly_sta->year = date("Y");
                $monthly_sta->hospital_id = $h_id;
                $monthly_sta->created_at = $data->created_at;
                foreach($data->monthly_statis as $column => $value){
                    $monthly_sta->$column = $value == '0' ? 0 : $value;
                }
                $monthly_sta->save();
            }
        }

        if(isset($data->doctor_perfo)){
            $d = DoctorPerformance::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($d)){
                $doctor = new DoctorPerformance();
                $doctor->added_by = 11;
                $doctor->month = date("m");
                $doctor->year = date("Y");
                $doctor->hospital_id = $h_id;
                $doctor->created_at = $data->created_at;
                foreach($data->doctor_perfo as $column => $value){
                    $doctor->$column = $value == '0' ? 0 : $value;
                }
                $doctor->save();
            }else{
                $doctor = DoctorPerformance::find($d->id);
                $doctor->added_by = 11;
                $doctor->month = date("m");
                $doctor->year = date("Y");
                $doctor->hospital_id = $h_id;
                $doctor->created_at = $data->created_at;
                foreach($data->doctor_perfo as $column => $value){
                    $doctor->$column = $value == '0' ? 0 : $value;
                }
                $doctor->save();
            }
        } 

        if(isset($data->immunization)){
            $immunizations = Immunization::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($immunizations)){
                $imm = new Immunization();
                $imm->added_by = 11;
                $imm->month = date("m");
                $imm->year = date("Y");
                $imm->created_at = $data->created_at;
                foreach($data->immunization as $column => $value){
                    $imm->$column = $value == '0' ? 0 : $value;
                }
                $imm->save();
            }else{
                $imm = Immunization::find($immunizations->id);
                $imm->added_by = 11;
                $imm->month = date("m");
                $imm->year = date("Y");
                $imm->created_at = $data->created_at;
                foreach($data->immunization as $column => $value){
                    $imm->$column = $value == '0' ? 0 : $value;
                }
                $imm->save();
            }
        }

        if(isset($data->total_facility_attendance)){
            $total_facility = TotalFacilityAttendance::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($total_facility)){
                $facility = new TotalFacilityAttendance();
                $facility->added_by = 11;
                $facility->month = date("m");
                $facility->year = date("Y");
                $facility->created_at = $data->created_at;
                foreach($data->total_facility_attendance as $column => $value){
                    $facility->$column = $value == '0' ? 0 : $value;
                }
                $facility->save();
            }else{
                $facility = TotalFacilityAttendance::find($total_facility->id);
                $facility->added_by = 11;
                $facility->month = date("m");
                $facility->year = date("Y");
                $facility->created_at = $data->created_at;
                foreach($data->total_facility_attendance as $column => $value){
                    $facility->$column = $value == '0' ? 0 : $value;
                }
                $facility->save();
            }
        }

        if(isset($data->patient_general_statistic_report)){
            $patient_general_statistic = PatientGeneralStatistics::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($patient_general_statistic)){
                $patient_general = new PatientGeneralStatistics();
                $patient_general->added_by = 11;
                $patient_general->month = date("m");
                $patient_general->year = date("Y");
                $patient_general->created_at = $data->created_at;
                foreach($data->patient_general_statistic_report as $column => $value){
                    $patient_general->$column = $value == '0' ? 0 : $value;
                }
                $patient_general->save();
            }else{
                $patient_general = PatientGeneralStatistics::find($patient_general_statistic->id);
                $patient_general->added_by = 11;
                $patient_general->month = date("m");
                $patient_general->year = date("Y");
                $patient_general->created_at = $data->created_at;
                foreach($data->patient_general_statistic_report as $column => $value){
                    $patient_general->$column = $value == '0' ? 0 : $value;
                }
                $patient_general->save();
            }
        }

        if(isset($data->patient_seen_clinic)){
            if(isset($data->patient_seen_clinic->data)){
                foreach($data->patient_seen_clinic->data as $key => $data23){
                    $patients = PatientSeen::where('created_at','LIKE','%'.$data->created_at.'%')->where('doctors_name', $key)->first();
    
                    if(empty($patients)){
                        $patient_seen = new PatientSeen();
                        $patient_seen->added_by = 11;
                        $patient_seen->month = date("m");
                        $patient_seen->year = date("Y");
                        $patient_seen->hospital_name = $h_name;
                        $patient_seen->hospital_id = $h_id;
                        $patient_seen->created_at = $data->created_at;
                        $patient_seen->doctors_name = $key;
                        $patient_seen->clinical_unit = $data23 == '0' ? 0 : $data23;
                        $patient_seen->save(); 
                    }else{
                        $patient_seen = PatientSeen::find($patients->id);
                        $patient_seen->added_by = 11;
                        $patient_seen->month = date("m");
                        $patient_seen->year = date("Y");
                        $patient_seen->hospital_name = $h_name;
                        $patient_seen->hospital_id = $h_id;
                        $patient_seen->created_at = $data->created_at;
                        $patient_seen->doctors_name = $key;
                        $patient_seen->clinical_unit = $data23 == '0' ? 0 : $data23;
                        $patient_seen->save();
                    }
                } 
            }
        }  

        if(isset($data->patient_seen_report)){
            foreach($data->patient_seen_report as $key => $data23){
                $patient = PatientSeenReport::where('created_at','LIKE','%'.$data->created_at.'%')->where('doctors_name', $key)->first();

                if(empty($patient)){
                    $patients_seen = new PatientSeenReport();
                    $patients_seen->added_by = 11;
                    $patients_seen->month = date("m");
                    $patients_seen->year = date("Y");
                    $patients_seen->hospital_name = $h_name;
                    $patients_seen->hospital_id = $h_id;
                    $patients_seen->created_at = $data->created_at;
                    $patients_seen->doctors_name = $key;
                    foreach($data23 as $column => $value){
                        $patients_seen->$column = $value == '0' ? 0 : $value;
                    }
                    $patients_seen->save(); 
                }else{
                    $patients_seen = PatientSeenReport::find($patient->id);
                    $patients_seen->added_by = 11;
                    $patients_seen->month = date("m");
                    $patients_seen->year = date("Y");
                    $patients_seen->hospital_name = $h_name;
                    $patients_seen->hospital_id = $h_id;
                    $patients_seen->created_at = $data->created_at;
                    $patients_seen->doctors_name = $key;
                    foreach($data23 as $column => $value){
                        $patients_seen->$column = $value == '0' ? 0 : $value;
                    }
                    $patients_seen->save();
                }
            } 

            $delete_doctor = PatientSeenReport::where('total',0)->delete();
        }

        if(isset($data->family_panning)){
            $family_plan = FamilyPlanningRecordOffice::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($family_plan)){
                $family = new FamilyPlanningRecordOffice();
                $family->added_by = 11;
                $family->month = date("m");
                $family->year = date("Y");
                $family->hospital_id = $h_id;
                $family->created_at = $data->created_at;
                foreach($data->family_panning as $column => $value){
                    $family->$column = $value == '0' ? 0 : $value;
                }
                $family->save();
            }else{
                $family = FamilyPlanningRecordOffice::find($family_plan->id);
                $family->added_by = 11;
                $family->month = date("m");
                $family->year = date("Y");
                $family->hospital_id = $h_id;
                $family->created_at = $data->created_at;
                foreach($data->family_panning as $column => $value){
                    $family->$column = $value == '0' ? 0 : $value;
                }
                $family->save();
            }
        }

        if(isset($data->health_insurance)){
            $health = HealthInsurance::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($health)){
                $h_insurance = new HealthInsurance();
                $h_insurance->added_by = 11;
                $h_insurance->month = date("m");
                $h_insurance->year = date("Y");
                $h_insurance->hospital_id = $h_id;
                $h_insurance->created_at = $data->created_at;
                foreach($data->health_insurance as $column => $value){
                    $h_insurance->$column = $value == '0' ? 0 : $value;
                }
                $h_insurance->save();
            }else{
                $h_insurance = HealthInsurance::find($health->id);
                $h_insurance->added_by = 11;
                $h_insurance->month = date("m");
                $h_insurance->year = date("Y");
                $h_insurance->hospital_id = $h_id;
                $h_insurance->created_at = $data->created_at;
                foreach($data->health_insurance as $column => $value){
                    $h_insurance->$column = $value == '0' ? 0 : $value;
                }
                $h_insurance->save();
            }
        }

        if(isset($data->communicable_disease)){
            $communicable = CommunicableDisease::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($communicable)){
                $communicable_de = new CommunicableDisease();
                $communicable_de->added_by = 11;
                $communicable_de->month = date("m");
                $communicable_de->year = date("Y");
                $communicable_de->hospital_id = $h_id;
                $communicable_de->created_at = $data->created_at;
                foreach($data->communicable_disease as $column => $value){
                    $communicable_de->$column = $value == '0' ? 0 : $value;
                }
                $communicable_de->save();
            }else{
                $communicable_de = CommunicableDisease::find($communicable->id);
                $communicable_de->added_by = 11;
                $communicable_de->month = date("m");
                $communicable_de->year = date("Y");
                $communicable_de->hospital_id = $h_id;
                $communicable_de->created_at = $data->created_at;
                foreach($data->communicable_disease as $column => $value){
                    $communicable_de->$column = $value == '0' ? 0 : $value;
                }
                $communicable_de->save();
            }
        }

        if(isset($data->diagnosis_count)){
            $diagnosis = DiagnosisCount::where('created_at','LIKE','%'.$data->created_at.'%')->first();

            if(empty($diagnosis)){
                $diag_count = new DiagnosisCount();
                $diag_count->added_by = 11;
                $diag_count->hospital_id = $h_id;
                $diag_count->created_at = $data->created_at;
                foreach($data->diagnosis_count as $column => $value){
                    $diag_count->$column = $value == '0' ? 0 : $value;
                }
                $diag_count->save();
            }else{
                $diag_count = DiagnosisCount::find($diagnosis->id);
                $diag_count->added_by = 11;
                $diag_count->hospital_id = $h_id;
                $diag_count->created_at = $data->created_at;
                foreach($data->diagnosis_count as $column => $value){
                    $diag_count->$column = $value == '0' ? 0 : $value;
                }
                $diag_count->save();
            }
        }

        if($h_id != ''){
            $user_ids = User::select('id')->where("hospital_name",$h_id)->where('role','Admin')->get();

            $v = array();
            $whom_to_seen = '';
            foreach($user_ids as $key => $value){
                $v[] = "'".$value->id."'";
            }
            $whom_to_seen = implode(',',$v);
        }

        $notification = Notification::where('content','LIKE','%'.$data->created_at.'%')->first();
        if(empty($notification)){
            $noti = new Notification();
            $noti->hospital_id = $h_id;
            $noti->subject = 'Auto Sent Data'; 
            $noti->content = $h_name.' Hospital which Date '.$data->created_at.' Data Auto send by '.$data->admin_name;
            $noti->whom_to_seen = $whom_to_seen;
            $noti->created_at = date('Y-m-d H:m:i');
            $noti->save();      
        }
         

        $cronn = CronHistory::whereDate('created_at', $data->created_at)->first();
        if(empty($cronn)){
            $cron = new CronHistory();
            $cron->type = 'report cron';
            $cron->hospital_id = $h_id;
            $cron->created_at = $data->created_at;
            $cron->updated_at = $data->created_at;
            $cron->project_name = $data->project_name;
            $cron->save();
        } 
        
        return response()->json(['status' => true]);

    } 

    public function get_report_me(Request $request)
    {
        error_reporting(1);
        $data = json_decode($request[0]);
        $date = date('Y-m-d');
        if(isset($data->hospital_registration)){
            $reg = $data->hospital_registration;
            $hospital_exist = DB::table('hospitals')
                ->where('hospitals.registration_number', $reg)
                ->first();
        }

        if(count($hospital_exist) == 0){
            if(isset($data->hospital)){
                $hospitals = new Hospitals();
                $hospitals->added_by = 11;
                foreach($data->hospital as $column => $value){
                    $hospitals->$column = $value;
                }
                $hospitals->save();
                $h_id = $hospitals->id;
            }
        }else{
            $h_id = $hospital_exist->id;
        }

        $h_id = 7;

        $cron_history_today = CronHistory::where('cron_history.created_at','LIKE','%'.$date.'%')->where('cron_history.project_name', 'art')->get();
        
        $cron_history = CronHistory::where('cron_history.project_name', 'art old')->get();

        if(count($cron_history) > 0)
        {
            if(count($cron_history_today) == 0){
                if(isset($data->inpatient_record)){
        
                    $inpatient = new InpatientRecords();
                    $inpatient->added_by = 11;
                    $inpatient->month = date("m");
                    $inpatient->year = date("Y");
                    $inpatient->hospital_id = $h_id;
                    $inpatient->created_at = $data->created_at;
                    foreach($data->inpatient_record as $column => $value){
                        $inpatient->$column = $value == '0' ? 0 : $value;
                    }
                    $inpatient->save();
                }
                
                if(isset($data->accident_emergency)){
                    $accident = new AccidentEmergency();
                    $accident->added_by = 11;
                    $accident->month = date("m");
                    $accident->year = date("Y");
                    $accident->hospital_id = $h_id;
                    $accident->created_at = $data->created_at;
                    foreach($data->accident_emergency as $column => $value){
                        $accident->$column = $value == '0' ? 0 : $value;
                    }
                    $accident->save();
                }
                
                if(isset($data->general_outpatient)){
                    $general = new GeneralOutpatient();
                    $general->added_by = 11;
                    $general->month = date("m");
                    $general->year = date("Y");
                    $general->hospital_id = $h_id;
                    $general->created_at = $data->created_at;
                    foreach($data->general_outpatient as $column => $value){
                        $general->$column = $value == '0' ? 0 : $value;
                    }
                    $general->save();
                }
                
                if(isset($data->laboratory)){
                    $laboratories = new LaboratoryInvestigations();
                    $laboratories->added_by = 11;
                    $laboratories->month = date("m");
                    $laboratories->year = date("Y");
                    $laboratories->hospital_id = $h_id;
                    $laboratories->created_at = $data->created_at;
                    foreach($data->laboratory as $column => $value){
                        $laboratories->$column = $value;
                    }
                    $laboratories->save();
                }
                
                if(isset($data->oparetion)){
                    $oparetions = new Operations();
                    $oparetions->added_by = 11;
                    $oparetions->month = date("m");
                    $oparetions->year = date("Y");
                    $oparetions->hospital_id = $h_id;
                    $oparetions->created_at = $data->created_at;
                    foreach($data->oparetion as $column => $value){
                        $oparetions->$column = $value;
                    }
                    $oparetions->save();
                }
                
                if(isset($data->special_consultive)){
                    $special_consultives = new SpecialConsultiveClinics();
                    $special_consultives->added_by = 11;
                    $special_consultives->month = date("m");
                    $special_consultives->year = date("Y");
                    $special_consultives->hospital_id = $h_id;
                    $special_consultives->created_at = $data->created_at;
                    foreach($data->special_consultive as $column => $value){
                        $special_consultives->$column = $value;
                    }
                    $special_consultives->save();
                }
                
                if(isset($data->radiology)){
                    $radio = new RadioDiagnosis();
                    $radio->added_by = 11;
                    $radio->month = date("m");
                    $radio->year = date("Y");
                    $radio->hospital_id = $h_id;
                    $radio->created_at = $data->created_at;
                    foreach($data->radiology as $column => $value){
                        $radio->$column = $value;
                    }
                    $radio->save();
                }
                
                if(isset($data->maternity_returns)){
                    $maternity_return = new MaternityReturns();
                    $maternity_return->added_by = 11;
                    $maternity_return->month = date("m");
                    $maternity_return->year = date("Y");
                    $maternity_return->hospital_id = $h_id;
                    $maternity_return->created_at = $data->created_at;
                    foreach($data->maternity_returns as $column => $value){
                        $maternity_return->$column = $value;
                    }
                    $maternity_return->save();
                }
        
                if(isset($data->immunization_clinic)){
                    $immunization = new ImmunizationClinic();
                    $immunization->added_by = 11;
                    $immunization->month = date("m");
                    $immunization->year = date("Y");
                    $immunization->hospital_id = $h_id;
                    $immunization->created_at = $data->created_at;
                    foreach($data->immunization_clinic as $column => $value){
                        $immunization->$column = $value;
                    }
                    $immunization->save();
                }
        
                if(isset($data->monthly_statis)){
                    $monthly_sta = new MonthlyHospitalStatistics();
                    $monthly_sta->added_by = 11;
                    $monthly_sta->month = date("m");
                    $monthly_sta->year = date("Y");
                    $monthly_sta->hospital_id = $h_id;
                    $monthly_sta->created_at = $data->created_at;
                    foreach($data->monthly_statis as $column => $value){
                        $monthly_sta->$column = $value;
                    }
                    $monthly_sta->save();
                }
        
                if(isset($data->doctor_perfo)){
                    $doctor = new DoctorPerformance();
                    $doctor->added_by = 11;
                    $doctor->month = date("m");
                    $doctor->year = date("Y");
                    $doctor->hospital_id = $h_id;
                    $doctor->created_at = $data->created_at;
                    foreach($data->doctor_perfo as $column => $value){
                        $doctor->$column = $value;
                    }
                    $doctor->save();
                } 
        
                if(isset($data->immunization)){
                    $imm = new Immunization();
                    $imm->added_by = 11;
                    $imm->month = date("m");
                    $imm->year = date("Y");
                    $imm->created_at = $data->created_at;
                    foreach($data->immunization as $column => $value){
                        $imm->$column = $value;
                    }
                    $imm->save();
                } 
    
                $cron = new CronHistory();
                $cron->type = 'report cron';
                $cron->hospital_id = $h_id;
                $cron->created_at = $data->created_at;
                $cron->updated_at = $data->created_at;
                $cron->project_name = $data->project_name;
                $cron->save();
            }
            
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    } 

    public function other_ehr_report(Request $request)
    {
        header('Access-Control-Allow-Origin: *');  
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        
        $system_1_api = 'MmddP6aERfO0Ij3dg3c7x1';
        $system_2_api = 'MmddP6aERfO0Ij3dg3c7x2';
        $system_3_api = 'MmddP6aERfO0Ij3dg3c7x3';
        $api_key = $request->api_key;
        $system_type = $request->system_type;
        $demo_male = $request->demo_male;
        $demo_female = $request->demo_female;
        $demo_total = $request->demo_total;
        if($system_type == 1){
            if($system_1_api == $api_key){

                $new_history = new HistoryApiCall();
                $new_history->system_id = $system_type == '' ? 1 : $system_type;
                $new_history->api_key = $api_key;
                $new_history->save();

                $demo_entry = new DemoEntryTable();
                $demo_entry->added_by = 11;
                $demo_entry->hospital_id = 7;
                $demo_entry->system_id = $system_type == '' ? 1 : $system_type;
                $demo_entry->month = date('m');
                $demo_entry->year = date('Y');
                $demo_entry->demo_male = $demo_male == '' ? 0 : $demo_male;
                $demo_entry->demo_female = $demo_female == '' ? 0 : $demo_female;
                $demo_entry->demo_total = $demo_total == '' ? 0 : $demo_total;
                $demo_entry->save();

                return response()->json(['status' => true,'message'=>'push data sucessfully 1']);
            }else{
                return response()->json(['status' => false,'message'=>'Api Key is not Match']);
            }
        }
        if($system_type == 2){
            if($system_2_api == $api_key){
                $new_history = new HistoryApiCall();
                $new_history->system_id = $system_type == '' ? 2 : $system_type;
                $new_history->api_key = $api_key;
                $new_history->save();

                $demo_entry = new DemoEntryTable();
                $demo_entry->added_by = 11;
                $demo_entry->hospital_id = 6;
                $demo_entry->system_id = $system_type == '' ? 2 : $system_type;
                $demo_entry->month = date('m');
                $demo_entry->year = date('Y');
                $demo_entry->demo_male = $demo_male == '' ? 0 : $demo_male;
                $demo_entry->demo_female = $demo_female == '' ? 0 : $demo_female;
                $demo_entry->demo_total = $demo_total == '' ? 0 : $demo_total;
                $demo_entry->save();

                return response()->json(['status' => true,'message'=>'push data sucessfully 2']);
            }else{
                return response()->json(['status' => false,'message'=>'Api Key is not Match']);
            }
        }
        if($system_type == 3){
            if($system_3_api == $api_key){
                $new_history = new HistoryApiCall();
                $new_history->system_id = $system_type == '' ? 3 : $system_type;
                $new_history->api_key = $api_key;
                $new_history->save();

                $demo_entry = new DemoEntryTable();
                $demo_entry->added_by = 11;
                $demo_entry->hospital_id = 5;
                $demo_entry->system_id = $system_type == '' ? 3 : $system_type;
                $demo_entry->month = date('m');
                $demo_entry->year = date('Y');
                $demo_entry->demo_male = $demo_male == '' ? 0 : $demo_male;
                $demo_entry->demo_female = $demo_female == '' ? 0 : $demo_female;
                $demo_entry->demo_total = $demo_total == '' ? 0 : $demo_total;
                $demo_entry->save();
                
                return response()->json(['status' => true,'message'=>'push data sucessfully 3']);
            }else{
                return response()->json(['status' => false,'message'=>'Api Key is not Match']);
            }
        }

        return response()->json(['status' => true,'message'=>'push data sucessfully']);
    }
}     