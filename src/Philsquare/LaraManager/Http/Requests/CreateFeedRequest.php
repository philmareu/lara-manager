<?php namespace Philsquare\LaraManager\Http\Requests;

class CreateFeedRequest extends Request {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255|unique:laramanager_feeds,title',
            'description' => 'required|max:255',
            'url' => 'required|max:255|url',
            'slug' => 'required|max:255|unique:laramanager_feeds,slug',
            'model' => 'required|max:255',
            'language' => 'required|max:255',
            'copyright' => 'required|max:255',
            'ttl' => 'required|integer'
        ];
    }

}