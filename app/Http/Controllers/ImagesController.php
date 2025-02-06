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
use App\Exceptions\FileNotFoundException;

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
            return response()->json($result, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }

    }

    public function imagesGetAll()
    {
        try {
            $input = Request::all();

            $search = $input['search'];

            $take = $input['take'] ?? 10;

            $skip = $input['skip'] ?? 0;

            $this->validateGetAllInputData($take, $skip);
            $take = (int) $take;
            $skip = (int) $skip;
            $result = $this->imageService->getAll($search, $take, $skip);

            return response()->json([$result], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function imagesDelete($id)
    {
        try {
            $input = Request::all();

            $this->validateDeleteInputData($id);
            $result = $this->imageService->delete($id);
            return response($result);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function imagesGet($id)
    {
        try {
            $input = Request::all();

            $this->validateGetInputData($id);
            $result = $this->imageService->get($id);

            return response($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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

    private function validateGetAllInputData($take, $skip)
    {
        if ((!ctype_digit($take) && !is_int($take)) || $take < 0) {
            throw new \InvalidArgumentException('Incorrect data');
        }
        if ((!ctype_digit($skip) && !is_int($skip)) || $skip < 0) {
            throw new \InvalidArgumentException('Incorrect data');
        }
    }

    private function validateDeleteInputData($id)
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            throw new \InvalidArgumentException('Invalid ID format');
        }
    }

    private function validateGetInputData($id)
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            throw new \InvalidArgumentException('Invalid ID format');
        }
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
