<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Mobile API",
 *   description="All mobile API endpoints for Athanasius Scouts",
 *   @OA\Contact(email="support@example.com", name="Athanasius Scouts")
 * )
 *
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Attendance", description="Endpoints related to attendance management")
 * @OA\Tag(name="Curricula", description="Endpoints related to curricula management")
 */
class MobileApiDocs {}