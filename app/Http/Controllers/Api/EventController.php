<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;

class EventController extends Controller
{
    use CanLoadRelationships;
    private array $relations = ['user', 'attendees', 'attendees.user'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');
    }
    public function index()
    {
        $query = $this->loadRelationships(Event::query());
        return EventResource::collection($query->latest()->paginate());
    }


    public function store(Request $request)
    {

        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

        return $event;






        // $event = Event::create([

        //         'name' => request('name'),
        //         'description' => request('description'),
        //         'start_time' => request('start_time'),
        //         'end_time' => request('end_time'),


        //     'user_id' => $request->user()->id
        // ]);
        // return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\Event $event)
    {
        // $event->load('attendee');
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        if(Gate::denies('update-event', $event)){
            abort(403);
        }
        $event->update([
            'name' => request('name'),
            'description' => request('description'),
            'start_time' => request('start_time'),
            'end_time' => request('end_time'),
        ]);
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json([
            'massage' => "OK, i'am delete, and what now? You take what you want while killing me?"
        ]);
    }
}
