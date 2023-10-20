<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use App\KpiOutcomes1;
use App\KpiOutcomes2;
use App\KpiOutcomes3;
use App\KpiOutcomes4;
use App\KpiOutcomes5;
use App\KpiOutputOutcoes1;
use App\KpiOutputOutcoes2;
use App\KpiOutputOutcoes3;
use App\KpiOutputOutcoes4;
use App\KpiOutputOutcoes5;

class KpisController extends Controller
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
    
    // Kpi Outcome 1 function start
    public function outcome1(Request $request)
    {
         $userId = Auth::user()->id; 
        $data = KpiOutcomes1::with('addedBy')->where('added_by',$userId)->orderBy('id','desc')->paginate(10);
        return view('kpi-outcomes.outcome1.view', ['data' => $data]);
    }
    public function displayOutcome1($id)
    {
        $data = KpiOutcomes1::find($id);
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome1.display', ['data' => $data]);
    }
    public function deleteOutcome1($id)
    {
        $data = KpiOutcomes1::find($id)->delete();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In KPI Outcome 1';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpis/outcome1';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('outcome1');
    }
    public function addOutcome1(Request $request)
    {
        return view('kpi-outcomes.outcome1.add');
    }
    public function editOutcome1($id)
    {
        $data = KpiOutcomes1::find($id);
        $id = $data['id'];
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome1.edit', ['data' => $data,'id'=>$id]);
    }
    public function insertOutcome1(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = new KpiOutcomes1;
        $outcome->added_by = $userId;
        $outcome->outcome = json_encode($postData['outcome1']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Outcome Add In KPI Outcome 1';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome1';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('outcome1');
    }
    public function updateOutcome1($id,Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = KpiOutcomes1::find($id);
        $outcome->outcome = json_encode($postData['outcome1']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Outcome Edit In KPI Outcome 1';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome1';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record updated successfully!');
        return redirect()->route('outcome1');
    }
    // Kpi Outcome 1 function end
    // Kpi Outcome 2 function start
    public function outcome2(Request $request)
    {
        $userId = Auth::user()->id;   
        $data = KpiOutcomes2::with('addedBy')->where('added_by',$userId)->orderBy('id','desc')->paginate(10);
        return view('kpi-outcomes.outcome2.view', ['data' => $data]);
    }
    public function displayOutcome2($id)
    {
        $data = KpiOutcomes2::find($id);
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome2.display', ['data' => $data]);
    }
    public function deleteOutcome2($id)
    {
        $data = KpiOutcomes2::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In KPI Outcome 2';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpis/outcome2';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('outcome2');
    }
    public function addOutcome2(Request $request)
    {
        return view('kpi-outcomes.outcome2.add');
    }
    public function editOutcome2($id)
    {
        $data = KpiOutcomes2::find($id);
        $id = $data['id'];
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome2.edit', ['data' => $data,'id'=>$id]);
    }
    public function insertOutcome2(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = new KpiOutcomes2;
        $outcome->added_by = $userId;
        $outcome->outcome = json_encode($postData['outcome2']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Outcome Add In KPI Outcome 2';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome2';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('outcome2');
    }
    public function updateOutcome2($id,Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = KpiOutcomes2::find($id);
        $outcome->outcome = json_encode($postData['outcome2']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Outcome Edit In KPI Outcome 2';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome2';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record updated successfully!');
        return redirect()->route('outcome2');
    }
    // Kpi Outcome 2 function end
    // 
    // Kpi Outcome 3 function start
    public function outcome3(Request $request)
    {
        $userId = Auth::user()->id;   
        $data = KpiOutcomes3::with('addedBy')->where('added_by',$userId)->orderBy('id','desc')->paginate(10);
        return view('kpi-outcomes.outcome3.view', ['data' => $data]);
    }
    public function displayOutcome3($id)
    {
        $data = KpiOutcomes3::find($id);
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome3.display', ['data' => $data]);
    }
    public function deleteOutcome3($id)
    {
        $data = KpiOutcomes3::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In KPI Outcome 3';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpis/outcome3';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('outcome3');
    }
    public function addOutcome3(Request $request)
    {
        return view('kpi-outcomes.outcome3.add');
    }
    public function editOutcome3($id)
    {
        $data = KpiOutcomes3::find($id);
        $id = $data['id'];
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome3.edit', ['data' => $data,'id'=>$id]);
    }
    public function insertOutcome3(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = new KpiOutcomes3;
        $outcome->added_by = $userId;
        $outcome->outcome = json_encode($postData['outcome3']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Outcome Add In KPI Outcome 3';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome3';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('outcome3');
    }
    public function updateOutcome3($id,Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = KpiOutcomes3::find($id);
        $outcome->outcome = json_encode($postData['outcome3']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Outcome Edit In KPI Outcome 3';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome3';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record updated successfully!');
        return redirect()->route('outcome3');
    }
    // Kpi Outcome 3 function end
    // 
    // Kpi Outcome 4 function start
    public function outcome4(Request $request)
    {
        $userId = Auth::user()->id;   
        $data = KpiOutcomes4::with('addedBy')->where('added_by',$userId)->orderBy('id','desc')->paginate(10);
        return view('kpi-outcomes.outcome4.view', ['data' => $data]);
    }
    public function displayOutcome4($id)
    {
        $data = KpiOutcomes4::find($id);
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome4.display', ['data' => $data]);
    }
    public function deleteOutcome4($id)
    {
        $data = KpiOutcomes4::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In KPI Outcome 4';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpis/outcome4';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('outcome4');
    }
    public function addOutcome4(Request $request)
    {
        return view('kpi-outcomes.outcome4.add');
    }
    public function editOutcome4($id)
    {
        $data = KpiOutcomes4::find($id);
        $id = $data['id'];
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome4.edit', ['data' => $data,'id'=>$id]);
    }
    public function insertOutcome4(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = new KpiOutcomes4;
        $outcome->added_by = $userId;
        $outcome->outcome = json_encode($postData['outcome4']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Outcome Add In KPI Outcome 4';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome4';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('outcome4');
    }
    public function updateOutcome4($id,Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = KpiOutcomes4::find($id);
        $outcome->outcome = json_encode($postData['outcome4']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Outcome Edit In KPI Outcome 4';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome4';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record updated successfully!');
        return redirect()->route('outcome4');
    }
    // Kpi Outcome 4 function end
    // 
    // Kpi Outcome 5 function start
    public function outcome5(Request $request)
    {
        $userId = Auth::user()->id;   
        $data = KpiOutcomes5::with('addedBy')->where('added_by',$userId)->orderBy('id','desc')->paginate(10);
        return view('kpi-outcomes.outcome5.view', ['data' => $data]);
    }
    public function displayOutcome5($id)
    {
        $data = KpiOutcomes5::find($id);
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome5.display', ['data' => $data]);
    }
    public function deleteOutcome5($id)
    {
        $data = KpiOutcomes5::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Record Delete In KPI Outcome 5';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpis/outcome5';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('outcome5');
    }
    public function addOutcome5(Request $request)
    {
        return view('kpi-outcomes.outcome5.add');
    }
    public function editOutcome5($id)
    {
        $data = KpiOutcomes5::find($id);
        $id = $data['id'];
        $data = json_decode($data['outcome'],true);
        return view('kpi-outcomes.outcome5.edit', ['data' => $data,'id'=>$id]);
    }
    public function insertOutcome5(Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = new KpiOutcomes5;
        $outcome->added_by = $userId;
        $outcome->outcome = json_encode($postData['outcome5']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Outcome Add In KPI Outcome 5';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome5';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('outcome5');
    }
    public function updateOutcome5($id,Request $request)
    {
        $userId = Auth::user()->id;
        $request->flash();
        $postData = $request->all();
        
        $outcome = KpiOutcomes5::find($id);
        $outcome->outcome = json_encode($postData['outcome5']);
        $outcome->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Outcome Edit In KPI Outcome 5';
        $data1['record_id'] = $outcome->id;
        $data1['route_name'] = 'kpis/outcome5';
        $data1['type'] = 'kpi';

        activity($data1);

        Session::flash('successMessage', 'Record updated successfully!');
        return redirect()->route('outcome5');
    }
    // Kpi Outcome 5 function end

        //     //output--outcomes--4
        //    public function outcome4(Request $request)
        //    {
        //        return view('kpi-outcomes.outcome4.add');
        //    }
        //    public function InsertOutcome4(Request $request)
        //    {
        //        $userId = Auth::user()->id;
        //        $request->flash();
        //        $postData = $request->all();
        //        
        //        $outcome = new KpiOutcomes4;
        //        $outcome->added_by = $userId;
        //        $outcome->outcome = json_encode($postData['outcome4']);
        //        $outcome->save();
        //
        //
        //        Session::flash('successMessage', 'Record added successfully!');
        //        return redirect()->route('outcome4');
        //    }
            ////output--outcomes--5
        //    public function outcome5(Request $request)
        //    {
        //        return view('kpi-outcomes.outcome5.add');
        //    }
        //    public function InsertOutcome5(Request $request)
        //    {
        //        $userId = Auth::user()->id;
        //        $request->flash();
        //        $postData = $request->all();
        //        
        //        $outcome = new KpiOutcomes5;
        //        $outcome->added_by = $userId;
        //        $outcome->outcome = json_encode($postData['outcome5']);
        //        $outcome->save();
        //
        //
        //        Session::flash('successMessage', 'Record added successfully!');
        //        return redirect()->route('outcome5');
        //    }

    ////output--outcomes

    ////output--outcomes--1

    public function kpiOutputOutcomesOneView()
    {
        $userId = Auth::user()->id;      
        $data = KpiOutputOutcoes1::with('addedBy')->where('added_by',$userId)->paginate(10);
        return view('kpi-output.kpi-outcomes-1.view', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesOneDisplay($id)
    {
       
        $data = KpiOutputOutcoes1::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-1.display', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesOneAdd(Request $request)
    {
        return view('kpi-output.kpi-outcomes-1.add');
    }
    
    public function kpiOutputOutcomesOneinsert(Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
         $data = new KpiOutputOutcoes1;
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome1']);
        $data->save();

        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Output Outcome Add In KPI Output Outcome 1';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-1';
        $data1['type'] = 'kpi';

        activity($data1);
        
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('kpi-output-one.view');
    }
    
    public function kpiOutputOutcomesOneEdit($id)
    {
         
      
        $data = KpiOutputOutcoes1::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-1.edit', ['data' => $data,'id'=>$id]);
    }
     
    
    public function kpiOutputOutcomesOneUpdate($id,Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
        $data =  KpiOutputOutcoes1 ::find($id);
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome1']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Edit In KPI Output Outcome 1';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-1';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record update successfully!');
        return redirect()->route('kpi-output-one.view');
    }
    
    public function kpiOutputOutcomesOneDelete($id)
    {
        
        KpiOutputOutcoes1::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Delete In KPI Output Outcome 1';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-1';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('kpi-output-one.view');
    }
    
    
    ////output--outcomes--2
     public function kpiOutputOutcomesTwoView()
    {
        $userId = Auth::user()->id;       
        $data = KpiOutputOutcoes2::with('addedBy')->where('added_by',$userId)->paginate(10);
        return view('kpi-output.kpi-outcomes-2.view', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesTwoDisplay($id)
    {
       
        $data = KpiOutputOutcoes2::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-2.display', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesTwoAdd(Request $request)
    {
        return view('kpi-output.kpi-outcomes-2.add');
    }
    
    public function kpiOutputOutcomesTwoinsert(Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
         $data = new KpiOutputOutcoes2;
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome2']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Output Outcome Add In KPI Output Outcome 2';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-2';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('kpi-output-two.view');
    }
    
    public function kpiOutputOutcomesTwoEdit($id)
    {
         
      
        $data = KpiOutputOutcoes2::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-2.edit', ['data' => $data,'id'=>$id]);
    }
     
    public function kpiOutputOutcomesTwoUpdate($id,Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
        $data =  KpiOutputOutcoes2 ::find($id);
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome2']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Edit In KPI Output Outcome 2';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-2';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record update successfully!');
        return redirect()->route('kpi-output-two.view');
    }
    
    public function kpiOutputOutcomesTwoDelete($id)
    {
        
        KpiOutputOutcoes2::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Delete In KPI Output Outcome 2';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-2';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('kpi-output-two.view');
    }
    ////output--outcomes--3
    
    public function kpiOutputOutcomesThreeView()
    {
        $userId = Auth::user()->id;     
        $data = KpiOutputOutcoes3::with('addedBy')->where('added_by',$userId)->paginate(10);
        return view('kpi-output.kpi-outcomes-3.view', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesThreeDisplay($id)
    {
       
        $data = KpiOutputOutcoes3::find($id);
        $data = json_decode($data['output'],true);ew('kpi-output.kpi-outcomes-3.display', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesThreeAdd(Request $request)
    {
        return view('kpi-output.kpi-outcomes-3.add');
    }
    
    public function kpiOutputOutcomesThreeinsert(Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
         $data = new KpiOutputOutcoes3;
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome3']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Output Outcome Add In KPI Output Outcome 3';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-3';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('kpi-output-three.view');
    }
    
    public function kpiOutputOutcomesThreeEdit($id)
    {
         
      
        $data = KpiOutputOutcoes3::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-3.edit', ['data' => $data,'id'=>$id]);
    }
     
    public function kpiOutputOutcomesThreeUpdate($id,Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
        $data =  KpiOutputOutcoes3 ::find($id);
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome3']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Edit In KPI Output Outcome 3';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-3';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record update successfully!');
        return redirect()->route('kpi-output-three.view');
    }
    
    public function kpiOutputOutcomesThreeDelete($id)
    {
        
        KpiOutputOutcoes3::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Delete In KPI Output Outcome 3';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-3';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('kpi-output-three.view');
    }
    ////output--outcomes--4
    
    public function kpiOutputOutcomesFourView()
    {
        $userId = Auth::user()->id;      
        $data = KpiOutputOutcoes4::with('addedBy')->where('added_by',$userId)->paginate(10);
        return view('kpi-output.kpi-outcomes-4.view', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesFourDisplay($id)
    {
       
        $data = KpiOutputOutcoes4::find($id);
        $data = json_decode($data['output'],true);

        return view('kpi-output.kpi-outcomes-4.display', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesFourAdd(Request $request)
    {
        return view('kpi-output.kpi-outcomes-4.add');
    }
    
    public function kpiOutputOutcomesFourinsert(Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
         $data = new KpiOutputOutcoes4;
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome4']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Output Outcome Add In KPI Output Outcome 4';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-4';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('kpi-output-four.view');
    }
    
    public function kpiOutputOutcomesFourEdit($id)
    {
        $data = KpiOutputOutcoes4::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-4.edit', ['data' => $data,'id'=>$id]);
    }
     
    public function kpiOutputOutcomesFourUpdate($id,Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
        $data =  KpiOutputOutcoes4 ::find($id);
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome4']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Edit In KPI Output Outcome 4';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-4';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record update successfully!');
        return redirect()->route('kpi-output-four.view');
    }
    
    public function kpiOutputOutcomesFourDelete($id)
    {
        
        KpiOutputOutcoes4::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Delete In KPI Output Outcome 4';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-4';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('kpi-output-four.view');
    }
    ////output--outcomes--5
    
    public function kpiOutputOutcomesFiveView()
    {
        $userId = Auth::user()->id;       
        $data = KpiOutputOutcoes5::with('addedBy')->where('added_by',$userId)->paginate(10);
        return view('kpi-output.kpi-outcomes-5.view', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesFiveDisplay($id)
    {
       
        $data = KpiOutputOutcoes5::find($id);
        $data = json_decode($data['output'],true);

        return view('kpi-output.kpi-outcomes-5.display', ['data' => $data]);
    }
    
    public function kpiOutputOutcomesFiveAdd(Request $request)
    {
        return view('kpi-output.kpi-outcomes-5.add');
    }
    
    public function kpiOutputOutcomesfiveinsert(Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
         $data = new KpiOutputOutcoes5;
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome5']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'New Output Outcome Add In KPI Output Outcome 5';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-5';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record added successfully!');
        return redirect()->route('kpi-output-five.view');
    }
    
    public function kpiOutputOutcomesFiveEdit($id)
    {
        $data = KpiOutputOutcoes5::find($id);
        $data = json_decode($data['output'],true);
        return view('kpi-output.kpi-outcomes-5.edit', ['data' => $data,'id'=>$id]);
    }
     
    public function kpiOutputOutcomesFiveUpdate($id,Request $request)
    {
         $request->flash();
        $postData = $request->all();
        $userId = Auth::user()->id;
        
       
        $data =  KpiOutputOutcoes5 ::find($id);
        $data->added_by = $userId;
        $data->output = json_encode($postData['outcome5']);
        $data->save();
        
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Edit In KPI Output Outcome 5';
        $data1['record_id'] = $data->id;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-5';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('successMessage', 'Record update successfully!');
        return redirect()->route('kpi-output-five.view');
    }
    
    public function kpiOutputOutcomesFiveDelete($id)
    {
        KpiOutputOutcoes5::find($id)->delete();
        $data1['user_id'] = Auth::user()->id;
        $data1['activity_name'] = 'Output Outcome Delete In KPI Output Outcome 5';
        $data1['record_id'] = 0;
        $data1['route_name'] = 'kpi-output/kpi-outcomes-5';
        $data1['type'] = 'kpi';

        activity($data1);
        Session::flash('errorMessage', 'Record deleted successfully!');

        return redirect()->route('kpi-output-five.view');
    }
    
   
}
