<?php namespace Philsquare\LaraManager\Http\Controllers; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Philsquare\LaraManager\Form\FormProcessor;
use Philsquare\LaraManager\Http\Requests\UpdateImageRequest;
use Philsquare\LaraManager\Http\Requests\UploadImageRequest;
use Philsquare\LaraManager\Models\Image;

class ImagesController extends Controller {

    protected $formProcessor;

    protected $image;

    protected $imageFolder = 'laramanager/images/';

    public function __construct(FormProcessor $formProcessor, Image $image)
    {
        $this->formProcessor = $formProcessor;
        $this->image = $image;
    }

    public function index(Request $request)
    {
        $images = $this->image->latest()->paginate(100);

        if($request->ajax())
        {
            $output['images'] = view('laramanager::browser.images', compact('images'))->render();
            return response()->json($output);
        }

        return view('laramanager::images.index', compact('images'));
    }

    public function edit($imageId)
    {
        $image = $this->image->findOrFail($imageId);
        $output['data']['html'] = view('laramanager::images.edit', compact('image'))->render();
        return response()->json($output);
    }

    public function update(UpdateImageRequest $request, $imageId)
    {
        $image = $this->image->findOrFail($imageId);
        if($image->filename != $request->get('filename'))
        {
            if(Storage::has($this->imageFolder . $request->get('filename'))) return response()->json(['errors' => ['Image Exists']]);

            Storage::move($this->imageFolder . $image->filename, $this->imageFolder . $request->get('filename'));
        }

        $image->update($request->all());

        $output['data'] = [
            'file' => $image,
            'url' => url('images/original/' . $image->filename)
        ];

        return response()->json($output);
    }

    public function search(Request $request)
    {
        $term = $request->term;

        $images = $this->image
            ->where('filename', 'LIKE', "%$term%")
            ->orWhere('title', 'LIKE', "%$term%")
            ->orWhere('alt', 'LIKE', "%$term%")
            ->orWhere('original_filename', 'LIKE', "%$term%")
            ->get();

        if($request->ajax())
        {
            $output['images'] = view('laramanager::browser.images', compact('images'))->render();
            return response()->json($output);
        }

        return view('laramanager::images.index', compact('images'));
    }

    public function imageBrowser(Request $request)
    {
        $funcNum = $request->has('CKEditorFuncNum') ? $request->get('CKEditorFuncNum') : '';

        $images = $this->image->latest()->paginate(100);
        return view('laramanager::browser.wysiwyg', compact('images', 'funcNum'));
    }

    public function upload(UploadImageRequest $request)
    {
        $upload = $request->file('image');
        $extension = $upload->getClientOriginalExtension();
        $originalName = str_replace('.' . $extension, '', $upload->getClientOriginalName());
        $filename = $this->formProcessor->processFile($upload, $this->imageFolder);
        $alt = str_replace('-', ' ', $originalName);
        $alt = str_replace('_', ' ', $alt);

        $image = $this->image->create([
            'filename' => $filename,
            'original_filename' => $upload->getClientOriginalName(),
            'alt' => ucwords($alt),
            'title' => ucwords($originalName),
            'size' => $upload->getClientSize()
        ]);

        $output['status'] = 'ok';
        $output['data'] = [
            'html' => view('laramanager::' . $request->get('view'), compact('image'))->render(),
            'image' => $image
        ];

        return response()->json($output);
    }

}