# External Image Server PHP Sample Project Description

[[_TOC_]]

## Project Generation

The project was generated from the [External Storage Api specification file](https://github.com/aurigma/CCHub-ExternalImageService-PHP-Sample/blob/master/api-spec/ExternalStorageApi.OpenApi3.swagger.json) using the open-source generator [open-api-generator](https://github.com/OpenAPITools/openapi-generator). The Laravel framework was chosen for generation.

To generate the project, use the following command:

```sh
openapi-generator-cli generate -i <path_to_your_file> -g php-laravel -o <folder_name>
```

After generation, the `routes/api.php` file contained an extra `/api` segment in all paths, causing its duplication in the links. This segment was removed to correct the paths:

Before: `http://localhost:8000/api/api/...`

After: `http://localhost:8000/api/...`

## Working with the API

The specification includes descriptions of six endpoints. The following details of each endpoint include:

- Purpose
- Security
- Path
- Request body and query parameters
- Response format and model
- Possible response codes

### *Info* Controller

#### Method: `infoGetInfo()`

Retrieves the current server version and information about implemented API functions.

- **URL**: `GET /api/image-storage/v1/info`
- **Authorization**: None
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation
    
        ```json
        {
            "name": "Image source",
            "version": "0.0.1",
            "features": [
                "AllowCreate",
                "AllowDelete",
                "AllowSearch"
            ]
        }
        ```

The `features` parameter provides information about the implemented functions of the service.

### *Images* Controller

#### Method: `imagesCreate()`

Adds a user's file to the file storage, creates a database record for the user's file, and generates a preview file, saving it to the storage.

- **URL**: `POST /api/image-storage/v1/images`
- **Authorization**: Bearer token (JWT)
- **Body** (multipart/form-data):

  ```
  file = binary file
  solveConflictStrategy = Overwrite | Rename | Abort | Skip
  ```

- **Query Parameters**: None
- **Responses**:

    - `201` - Successful creation

        ```json
        {
            "id": "Guid",
            "title": "File Name",
            "thumbnailUrl": "http://localhost:8000/api/previews/someGuidPreview"
        }
        ```

    - `400` - Issues with request parameters

        ```json
        {
            "message": "No file uploaded" | "message": "No strategy provided"
        }
        ```

    - `401` - Authorization issues

        ```json
        {
            "message": "Unauthenticated."
        }
        ```

    - `409` - Conflict detected, strategy not specified or Abort

        ```json
        {
            "message": "No strategy provided" | "message": "File already exists"
        }
        ```

#### Method: `imagesGetAll()`

Retrieves all user file records with filtering by string match in the file name and applying pagination.

- **URL**: `GET /api/image-storage/v1/images`
- **Authorization**: Bearer token (JWT)
- **Body**: None
- **Query Parameters**:

  ```
  search: <text>
  take: <number>
  skip: <number>
  ```

- **Responses**:
    - `200` - Successful operation
    
        ```json
        [
            {
                "id": "Guid1",
                "title": "File Name 1",
                "thumbnailUrl": "http://localhost:8000/api/previews/someGuidPreview1"
            },
            {
                "id": "Guid2",
                "title": "File Name 2",
                "thumbnailUrl": "http://localhost:8000/api/previews/someGuidPreview2"
            }
        ]
        ```

    - `400` - Issues with request parameters

        ```json
        {
            "message": "Incorrect data"
        }
        ```

    - `401` - Authorization issues

        ```json
        {
            "message": "Unauthenticated"
        }
        ```

#### Method: `imagesDelete(someId)`

Deletes a user's file and its preview from the storage and removes the file record from the database.

- **URL**: `DELETE /api/image-storage/v1/images/{someId}`
- **Authorization**: Bearer token (JWT)
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, file deleted
    - `400` - Issues with request parameters, invalid ID

        ```json
        {
            "message": "Invalid ID format"
        }
        ```

    - `401` - Authorization issues

        ```json
        {
            "message": "Unauthenticated."
        }
        ```

    - `404` - Resource not found, no file information in the database

        ```json
        {
            "message": "FileInfo is not found."
        }
        ```

#### Method: `imagesGet(someId)`

Retrieves information about a user's file from the database and a link to a preview file.

- **URL**: `GET /api/image-storage/v1/images/{someId}`
- **Authorization**: Bearer token (JWT)
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, file found

        ```json
        {
            "id": "Guid",
            "title": "File Name",
            "thumbnailUrl": "http://localhost:8000/api/previews/someGuidPreview"
        }
        ```

    - `400` - Issues with request parameters, invalid ID

        ```json
        {
            "message": "Invalid ID format"
        }
        ```

    - `401` - Authorization issues

        ```json
        {
            "message": "Unauthenticated."
        }
        ```

    - `404` - Resource not found, no file information in the database

        ```json
        {
            "message": "FileInfo is not found."
        }
        ```

#### Method: `imagesGetContent(someId)`

Retrieves a user's file by ID.

- **URL**: `GET /api/image-storage/v1/images/{someId}/content`
- **Authorization**: Bearer token (JWT)
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, file found, returns file stream
    - `400` - Issues with request parameters, invalid ID

        ```json
        {
            "message": "Invalid ID format"
        }
        ```

    - `401` - Authorization issues

        ```json
        {
            "message": "Unauthenticated."
        }
        ```

    - `404` - Resource not found, no file information in the database

        ```json
        {
            "message": "FileInfo is not found."
        }
        ```

#### General Principles:

- The `Headers` for all these endpoints should include:
    - `Accept = application/json`
    - `Authorization = Bearer <someToken>`
- A `POST` request saves the file in the system, creating a model that describes the file - ID, file name, and preview link. This model is returned in the response.
- If a `POST` request attempts to upload a file with a name that already exists in the system, a conflict will occur. This conflict is resolved using strategies. For more details about the strategies, refer to the section [Strategies for Handling Name Conflicts of Uploaded Files](#strategies-for-handling-name-conflicts-of-uploaded-files).
- To retrieve (or delete) a file, specify the file ID from the database in the request path.
- The file ID is in GUID format.
- The `title` displays the file name with its extension.
- The `thumbnail` displays the link to the client's preview file, created when the client sends the file in a `POST` request. The preview dimensions can be set in the `.env` file using the parameters `PREVIEW_WIDTH` and `PREVIEW_HEIGHT`.
- A `GET` request to retrieve content returns a file stream.

In addition to the endpoints described in the specification, the project includes four additional endpoints.

### *Previews* Controller

#### Method: `previewsGet(someId)`

Retrieves a preview image.

- **URL**: `GET /api/previews/{someId}`
- **Authorization**: None
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, preview file found, returns preview file stream
    - `400` - Issues with request parameters, invalid ID

        ```json
        {
            "message": "Invalid ID format"
        }
        ```

    - `404` - Resource not found, no file information in the database

        ```json
        {
            "message": "FileInfo is not found"
        }
        ```

### *JWTAuth* Controller

#### Method: `register()`

Registers a new user.

- **URL**: `POST /api/auth/register`
- **Authorization**: None
- **Body** (multipart/form-data):

  ```
  name = <your_value>
  email = <your_value> (must be unique)
  password = <your_value>
  ```

- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, user registered

        ```json
        {
            "message": "Successful registration!"
        }
        ```

#### Method: `login()`

Authenticates a user.

- **URL**: `POST /api/auth/login`
- **Authorization**: None
- **Body** (multipart/form-data):

  ```
  email = <your_value>
  password = <your_value>
  ```

- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, user authenticated

        ```json
        {
            "access_token": "<Token>",
            "token_type": "bearer",
            "expires_in": <Unix timestamp of token expiration>
        }
        ```

    - `401` - Authentication failed

        ```json
        {
            "error": "Unauthorized"
        }
        ```

#### Method: `logout()`

Ends the session of an authenticated user.

- **URL**: `POST /api/auth/logout`
- **Authorization**: Bearer token (JWT)
- **Body**: None
- **Query Parameters**: None
- **Responses**:

    - `200` - Successful operation, user session ended

        ```json
        {
            "message": "Successfully logged out"
        }
        ```

    - `401` - Authorization issues
    
        ```json
        {
            "error": "Unauthorized"
        }
        ```

#### Authentication and Registration Principles

- After ending the session of an authenticated user, the token becomes invalid. To end the session, call the `logout` endpoint and pass the session token in the `Authorization` header.
- The token becomes invalid after its expiration time (60 minutes). The token's expiration time can be changed in the `.env` file using the parameter `JWT_TTL`.
- During registration, fill in the fields `name`, `email` (unique values), and `password`.
- To log in, enter the previously provided values for `email` and `password`. After authentication, the user receives a token containing user information, including the user ID.

## Using JWT

For token management, the following package is used:

```
tymon/jwt-auth: ^1.0
```

During registration, user data is stored in the `users` table, with the user's `email` serving as a unique identifier. After authentication, the user receives a token used to access protected endpoints. The `AuthService` extracts the user `id` from the token, which is then passed to the main `ImageService`. The `id` is used for:

- Naming the user's folder
- Recording file information in the database
- Searching for file information by user `id`
- Storing user data separately

## File Handling Logic

* **File Upload**:

    * The file name and extension are saved in the database and used when saving to the user's internal folder on the server.
    * The system checks for existing file information in the database:

        * If a record is found, the conflict resolution strategy is applied. More details on conflict resolution can be found in the section [Strategies for Handling Name Conflicts of Uploaded Files](#strategies-for-handling-name-conflicts-of-uploaded-files).
        * If no record is found, the file is saved in the user's folder, and information about the file, including the preview, is recorded.

    * The response model `ImageInfoModel` is structured as follows:
    
        ```json
        {
            "id": "Guid",
            "title": "File Name",
            "thumbnailUrl": "http://localhost:8000/api/previews/someGuidPreview"
        }
        ```

* **Retrieving a List of File Information with Filtering and Pagination**:

  * Request parameters include `search` (matches full file names), `take` (limits the number of records, default is 10), and `skip` (skips the specified number of records).
  * A database query is constructed based on the user `id`.
  * The resulting list is formatted using the `ImageInfoModel` and returned to the user.

* **Deleting a File and Its Record**:

  * The file name is searched by `id` and user.
  * The paths to the file and its preview are constructed.
  * The file and its preview are deleted.
  * The file record is removed from the database.

* **Retrieving Information About a Single File**:

  * The file name is searched by `id` and user.
  * The path to the file is constructed.
  * The response model `ImageInfoModel` is formatted and returned to the user.

* **Retrieving a File (Displaying an Image)**:

  * The file name is searched by `id` and user.
  * The path to the file is constructed.
  * The file is returned.

## Error Descriptions

* `FileNotFoundException` - No file record found in the database.
* `Exception` - File not found in storage, file deletion error, server error.
* `ConflictException` - File name conflict.
* `InvalidArgumentException` - Incorrect request, missing file or strategy during upload, invalid data in `take` and `skip` parameters (numeric values expected, provided as strings but type-checked later).

## Preview Logic

Previews are created using the Customer's Canvas API. Detailed instructions on working with Customer's Canvas can be found [here](#authorization-in-customers-canvas).

The following packages are used to work with Customer's Canvas and create previews:

```
aurigma/php-design-atoms-client: ^2.1
aurigma/php-storefront-client: 2.0.1
```

* **File Upload (Endpoint for Saving Files)**:

  * An object of the `DesignAtomsImagesApi` class is created to access the `DesignAtomsApi` service and use the function that converts the file into a preview.
  * The file is converted from `UploadedFile` to `SplFileObject`.
  * A preview file is created using the `DesignAtomsImagesApi` class function, with the converted file as one of the arguments.
  * The preview is saved in the user's folder `storage/app/someUserId/preview`.

* **File Deletion**:

  * The preview is searched by file name.
  * The preview is deleted.

* **Retrieving a Preview File via a Public Endpoint**:

  * Access the link `http://localhost:8000/api/previews/{someGuidPreview}`.
  * The file name is searched by `id`.
  * The path to the file is constructed (for existence check).
  * The path to the preview is constructed.
  * The preview is returned.

## Strategies for Handling Name Conflicts of Uploaded Files

This API implements strategies to handle file name conflicts during uploads:

* **Overwrite** – Overwrites the file if it already exists. The old file is deleted, a new file with the same name is created, and the preview and timestamp in the database are updated.
* **Rename** – Uploads the file with a new name if a file with the same name already exists. A new record is created in the database, and a new preview is generated.
* **Abort** – Stops the upload if a file with the same name already exists. Returns an error.
* **Skip** – Does not upload the file if it already exists. Returns information about the existing file.

If no strategy is specified, the upload will be aborted in case of a name conflict.

## Authorization in Customers Canvas

The Customers Canvas system uses Identity Server 4 for the authorization process, supporting OAuth2 and Client Credentials flow using `clientId` and `clientSecret`.

To access Customers Canvas, use the `CC_HUB_API_URL`. The `.env` file should include the following parameters:

```
CC_HUB_API_URL=<YOUR_API_URL>
CC_HUB_CLIENT_ID=<YOUR_CLIENT_ID>
CC_HUB_CLIENT_SECRET=<YOUR_CLIENT_SECRET>
```

For more details on obtaining these parameters, refer to [this article](https://customerscanvas.com/dev/backoffice/auth.html).