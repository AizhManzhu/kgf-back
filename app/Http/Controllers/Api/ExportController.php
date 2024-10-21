<?php

namespace App\Http\Controllers\Api;

use App\Exports\PromocodeExport;
use App\Http\Controllers\Controller;
use App\Models\Promocode;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventMembersExport;
use App\Exports\MembersExport;
use App\Exports\Report;
use App\Exports\ResultOfCompetitionExport;
use App\Models\Competition;
use App\Models\Task;
use App\Models\ResultOfCompetition;
use App\Models\EventMember;
use App\Models\MemberTask;
use App\Models\Member;

class ExportController extends Controller
{
    public function exportEventMembers($eventId){
       return  Excel::download(new EventMembersExport($eventId, false), 'eventMembers.xlsx');
    }

    public function exportMembers(){
        return Excel::download(new MembersExport, 'members.xlsx');
    }

    public function exportReport(){
        return Excel::download(new Report, 'report.xlsx');
    }

    public function exportCompetitionMembers($eventId) {
        return Excel::download(new EventMembersExport($eventId, true), 'competitionMembers.xlsx');
    }

    public function exportPromocode()
    {
        return Excel::download(new PromocodeExport(), 'promocodeExport.csv');
    }

    public function exportLogCompetition($eventId){

        $members = EventMember::query()->with(array('member' => function($query) {
            $query->with('tasks');
            }))->where('is_participated', 1)->get()->pluck('member');

        foreach ($members as $member) {
            $tasks = $member->tasks;
            foreach ($tasks as $task) {
                $stageName = Competition::find($task->competition_id)->stage_name;
                $question = Task::find($task->task_id)->question;
                ResultOfCompetition::create([
                    'FIO' => $member->last_name." ".$member->first_name,
                    'phone' => $member->phone,
                    'email' => $member->email,
                    'stage' => $stageName,
                    'question' => $question,
                    'answer' => $task->log_answer === "Yes" ? 'Правильно' : 'Неправильно',
                    'time' => date_format($task->updated_at, 'H:i:s.u')
                ]);
            }
        }
         $result = Excel::download(new ResultOfCompetitionExport, 'logCompetition.xlsx');
         ResultOfCompetition::truncate();
         return $result;
    }

    public function exportResultOfCompetition($eventId)
    {
         $members = EventMember::query()->with(array('member' => function($query) {
            $query->with('tasks');
            }))->where('is_participated', 1)->get()->pluck('member');

        $competitionWithTask = Competition::with(['tasks' => function($query){$query->latest()->first();}])->latest()->first();
        foreach ($members as $member) {
            $memberTasks = $member->tasks;
            $memberTaskId = 0;
            foreach ($memberTasks as $memberTask) {
                if($memberTask->task_id == $competitionWithTask->tasks[0]->id && $memberTask->competition_id == $competitionWithTask->id && $memberTask->log_answer == 'Yes'){
                    $memberTaskId = $memberTask->id;
                }
                continue;
            }

            if($memberTaskId != 0){
                $resultOfTask = MemberTask::find($memberTaskId);
                $stageName = Competition::find($resultOfTask->competition_id)->stage_name;
                $question = Task::find($resultOfTask->task_id)->question;
                ResultOfCompetition::create([
                        'FIO' => $member->last_name." ".$member->first_name,
                        'phone' => $member->phone,
                        'email' => $member->email,
                        'stage' => $stageName,
                        'question' => $question,
                        'answer' => $resultOfTask->log_answer === "Yes" ? 'Правильно' : 'Неправильно',
                        'time' => date_format($resultOfTask->updated_at, 'H:i:s.u')
                ]);
            }
        }
        $result = Excel::download(new ResultOfCompetitionExport, 'resultOfCompetition.xlsx');
        ResultOfCompetition::truncate();
        return $result;
    }
}
