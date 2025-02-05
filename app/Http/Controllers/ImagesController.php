<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use app\Models\ImageInfoModel;
use app\Models\ImageFileInfoModel;
use Illuminate\Support\Str;
use Exception;
use app\Services\ImageService;
use App\Exceptions\ConflictException;

class ImagesController extends Controller
{
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function imagesCreate()
    {
        try {
            $inputFile = Request::file();
            $inputStrategy = Request::all();

            $this->validateCreationInputData($inputFile, $inputStrategy);

            $file = $inputFile['file'];
            $strategy = $inputStrategy['strategy'];

            $result = $this->imageService->create($file, $strategy);
            $output = $this->mapToDto($result);
            return response()->json($output, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (ConflictException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }

    }

    private function validateCreationInputData($inputFile, $inputStrategy)
    {
        if (!$inputFile || !isset($inputFile['file'])) {
            throw new \InvalidArgumentException('No file uploaded');
        }
        if (!$inputStrategy || !isset($inputStrategy['strategy'])) {
            throw new \InvalidArgumentException('No strategy provided');
        }
    }

    private function mapToDto($result)
    {
        return [
            'id' => $result->id,
            'title' => $result->title,
            'thumbnailUrl' => $result->thumbnailUrl,
        ];
    }


    public function imagesGetAll()
    {
        $input = Request::all();

        $search = $input['search'];

        $take = $input['take'] ?? 10;

        $skip = $input['skip'];

        $query = null;

        if ($search) {
            $query = ImageFileInfoModel::where('name', 'LIKE', "%$search%");
        } else {
            $query = ImageFileInfoModel::select('*');
        }

        if ($skip) {
            $query = $query->skip($skip)->take($take);
        } else {
            $query = $query->take($take);
        }

        $fileInfos = $query->get();

        return response()->json([$fileInfos], 200);
    }
    /**
     * Operation imagesDelete
     *
     * Deletes an image by ID..
     *
     * @param string $id Image ID in storage. (required)
     *
     * @return Http response
     */
    public function imagesDelete($id)
    {
        $input = Request::all();

        return response('How about implementing imagesDelete as a delete method ?');
    }
    /**
     * Operation imagesGet
     *
     * Returns an image description..
     *
     * @param string $id Image ID in storage. (required)
     *
     * @return Http response
     */
    public function imagesGet($id)
    {
        $input = Request::all();

        //path params validation


        //not path params validation

        // return response('How about implementing imagesGet as a get method ?');
        return response('id = ' . implode(',', $input) );
    }
    /**
     * Operation imagesGetContent
     *
     * Returns an image content by ID..
     *
     * @param string $id Image ID in storage. (required)
     *
     * @return Http response
     */
    public function imagesGetContent($id)
    {
        $input = Request::all();

        //path params validation


        //not path params validation

        return response('How about implementing imagesGetContent as a get method ?');
    }
    /**
     * Operation imagesGetContentUrl
     *
     * Returns an image content URL by ID..
     *
     * @param string $id Image ID in storage. (required)
     *
     * @return Http response
     */
    public function imagesGetContentUrl($id)
    {
        $input = Request::all();

        //path params validation


        //not path params validation

        return response('How about implementing imagesGetContentUrl as a get method ?');
    }
}
