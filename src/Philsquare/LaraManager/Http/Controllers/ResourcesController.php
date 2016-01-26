<?php

namespace Philsquare\LaraManager\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Philsquare\LaraForm\Services\FormProcessor;
use Philsquare\LaraManager\Models\File;
use Philsquare\LaraManager\Models\Object;

class ResourcesController extends Controller
{
    protected $request;

    protected $resource;

    protected $fields;

    protected $title;

    protected $modelsNamespace;

    protected $model;

    protected $form;

    public function __construct(Request $request, FormProcessor $form)
    {
        $this->resource = $request->segment(2);
        $this->fields = config('laramanager.resources.' . $this->resource . '.fields');
        $this->title = config('laramanager.resources.' . $this->resource . '.title');
        $this->modelsNamespace = config('laramanager.models_namespace') . '\\';
        $this->form = $form;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = $this->title;
        $fields = $this->fields;

        $select = ['id'];
        foreach($this->fields as $field)
        {
            if(isset($field['list']) && $field['list'] === true) $select[] = $field['name'];
        }

        $resource = $this->resource;
        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');
        $entities = $model::select($select)->get();

        return view('laramanager::resource.index', compact('resource', 'entities', 'fields', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = $this->title;
        $fields = $this->fields;
        $resource = $this->resource;

        $hasWysiwyg = false;

        foreach($fields as $field)
        {
            if($field['type'] == 'wysiwyg') $hasWysiwyg = true;
        }

        $files = File::latest()->get();

        return view('laramanager::resource.create', compact('resource', 'title', 'fields', 'hasWysiwyg', 'files'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validationRules($this->fields, 'store'));

        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');
        $entity = new $model;
        $attr = $request->all();

        foreach($this->fields as $field)
        {
            if($field['type'] == 'image')
            {
                if($request->hasFile($field['name']))
                {
                    $filename = $this->form->processFile($request->file($field['name']), 'images');
                    $attr[$field['name']] = $filename;
                }
            }

            if($field['type'] == 'password')
            {
                $attr[$field['name']] = bcrypt($request->get($field['name']));
            }
        }

        $entity = $entity->create($attr);

        foreach(config('laramanager.resources.' . $this->resource . '.objects') as $defaultObject)
        {
            $object = Object::where('slug', $defaultObject['type'])->first();

            $entity->objects()->attach($object->id, ['label' => $defaultObject['label']]);
        }

        if(method_exists($model, 'objects')) return redirect('admin/' . $this->resource . '/' . $entity->id)->with('success', 'Added');

        return redirect('admin/' . $this->resource . '/' . $entity->id)->with('success', 'Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($resourceId)
    {
        $title = $this->title . ' > View';
        $fields = $this->fields;
        $resource = $this->resource;
        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');
        $entity = $model::with('objects')->where('id', $resourceId)->first();
        $objects = Object::all();

        return view('laramanager::resource.show', compact('title', 'fields', 'resource', 'entity', 'objects'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($resourceId)
    {
        $hasWysiwyg = false;
        $title = $this->title;
        $fields = $this->fields;
        $resource = $this->resource;
        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');
        $entity = $model::find($resourceId);

        foreach($fields as $field)
        {
            if($field['type'] == 'wysiwyg') $hasWysiwyg = true;
        }

        $files = File::latest()->get();

        return view('laramanager::resource.edit', compact('title', 'fields', 'resource', 'entity', 'hasWysiwyg', 'files'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validationRules($this->fields, 'update'));

        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');
        $entity = (new $model)->findOrFail($id);

        $attributes = $request->all();

        foreach($this->fields as $field)
        {
            if($field['type'] == 'checkbox')
            {
                if(! $request->has($field['name'])) $attributes[$field['name']] = 0;
            }

            if($field['type'] == 'image')
            {
                if($request->hasFile($field['name']))
                {
                    $filename = $this->form->processFile($request->file($field['name']), 'images', $entity->{$field['name']});
                    $attributes[$field['name']] = $filename;
                }
            }

            if($field['type'] == 'password')
            {
                $attributes[$field['name']] = bcrypt($request->get($field['name']));
            }
        }

        $entity->update($attributes);

        if(method_exists($model, 'objects')) return redirect('admin/' . $this->resource . '/' . $entity->id)->with('success', 'Updated');

        return redirect()->back()->with('success', 'Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->modelsNamespace . config('laramanager.resources.' . $this->resource . '.model');

        $entity = (new $model)->findOrFail($id);
        if($entity->delete()) return response()->json(['status' => 'ok']);

        return response()->json(['status' => 'failed']);
    }

    public function uploads(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => $request->validation
        ]);

        if($validator->fails()) return response()->json(['status' => 'failed']);

        $model = $this->modelsNamespace . config('laramanager.resources.' . $request->resource . '.model');
        $reference = $request->name;

        $filename = $this->form->processFile($request->file('file'), 'files');

        $file = File::create(['filename' => $filename, 'type' => 'image']);

        $entity = (new $model)->findOrFail($request->entityId);
        $entity->photos()->attach($file->id);

        $output['status'] = 'ok';
        $output['data']['html'] = view('laraform::elements.form.displays.file', compact('file'))->render();

        return response()->json($output);
    }

    public function deleteFile(Request $request)
    {
        $model = $this->modelsNamespace . config('laramanager.resources.' . $request->resource . '.model');
        $entity = (new $model)->findOrFail($request->entityId);

        $entity->photos()->detach($request->id);

        return response()->json(['status' => 'ok']);
    }

    private function validationRules($fields, $operation)
    {
        foreach($fields as $settings)
        {
            $rules[$settings['name']] = is_array($settings['validation']) ? $settings['validation'][$operation] : $settings['validation'];
        }

        return isset($rules) ? $rules : [];
    }
}
