<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit26b060b64247a2a6e91b00a4357fb1ca
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MasterStudy\\Lms\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MasterStudy\\Lms\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'MasterStudy\\Lms\\Database\\AbstractQuery' => __DIR__ . '/../..' . '/includes/Database/AbstractQuery.php',
        'MasterStudy\\Lms\\Database\\CurriculumMaterial' => __DIR__ . '/../..' . '/includes/Database/CurriculumMaterial.php',
        'MasterStudy\\Lms\\Database\\CurriculumSection' => __DIR__ . '/../..' . '/includes/Database/CurriculumSection.php',
        'MasterStudy\\Lms\\Database\\Query' => __DIR__ . '/../..' . '/includes/Database/Query.php',
        'MasterStudy\\Lms\\Enums\\CourseStatus' => __DIR__ . '/../..' . '/includes/Enums/CourseStatus.php',
        'MasterStudy\\Lms\\Enums\\CurriculumMaterialType' => __DIR__ . '/../..' . '/includes/Enums/CurriculumMaterialType.php',
        'MasterStudy\\Lms\\Enums\\DurationMeasure' => __DIR__ . '/../..' . '/includes/Enums/DurationMeasure.php',
        'MasterStudy\\Lms\\Enums\\Enum' => __DIR__ . '/../..' . '/includes/Enums/Enum.php',
        'MasterStudy\\Lms\\Enums\\LessonType' => __DIR__ . '/../..' . '/includes/Enums/LessonType.php',
        'MasterStudy\\Lms\\Enums\\LessonVideoType' => __DIR__ . '/../..' . '/includes/Enums/LessonVideoType.php',
        'MasterStudy\\Lms\\Enums\\QuestionType' => __DIR__ . '/../..' . '/includes/Enums/QuestionType.php',
        'MasterStudy\\Lms\\Enums\\QuestionView' => __DIR__ . '/../..' . '/includes/Enums/QuestionView.php',
        'MasterStudy\\Lms\\Enums\\QuizStyle' => __DIR__ . '/../..' . '/includes/Enums/QuizStyle.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\ApproveController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/ApproveController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\CreateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/CreateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\GetController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/GetController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\ReplyController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/ReplyController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\SpamController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/SpamController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\TrashController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/TrashController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\UnapproveController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/UnapproveController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\UnspamController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/UnspamController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\UntrashController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/UntrashController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Comment\\UpdateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Comment/UpdateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\CourseBuilder\\GetSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/CourseBuilder/GetSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\AddNewController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/AddNewController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\CreateCategoryController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/CreateCategoryController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\CreateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/CreateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\CreateMaterialController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/CreateMaterialController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\CreateSectionController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/CreateSectionController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\DeleteMaterialController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/DeleteMaterialController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\DeleteSectionController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/DeleteSectionController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\GetCurriculumController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/GetCurriculumController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\ImportMaterialsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/ImportMaterialsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\ImportSearchController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/ImportSearchController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\UpdateMaterialController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/UpdateMaterialController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\Curriculum\\UpdateSectionController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/Curriculum/UpdateSectionController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\EditController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/EditController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\GetAnnouncementController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/GetAnnouncementController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\GetFaqSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/GetFaqSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\GetPricingSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/GetPricingSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\GetSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/GetSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateAccessSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateAccessSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateAnnouncementController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateAnnouncementController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateCertificateSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateCertificateSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateFaqSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateFaqSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateFilesSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateFilesSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdatePricingSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdatePricingSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateSettingsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateSettingsController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Course\\UpdateStatusController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Course/UpdateStatusController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\HealthCheckController' => __DIR__ . '/../..' . '/includes/Http/Controllers/HealthCheckController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Lesson\\CreateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Lesson/CreateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Lesson\\GetController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Lesson/GetController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Lesson\\UpdateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Lesson/UpdateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Media\\DeleteController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Media/DeleteController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Media\\UploadController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Media/UploadController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\CreateCategoryController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/CreateCategoryController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\CreateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/CreateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\DeleteController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/DeleteController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\GetCategoriesController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/GetCategoriesController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\GetController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/GetController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Question\\UpdateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Question/UpdateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Quiz\\CreateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Quiz/CreateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Quiz\\DeleteController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Quiz/DeleteController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Quiz\\GetController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Quiz/GetController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Quiz\\UpdateController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Quiz/UpdateController.php',
        'MasterStudy\\Lms\\Http\\Controllers\\Quiz\\UpdateQuestionsController' => __DIR__ . '/../..' . '/includes/Http/Controllers/Quiz/UpdateQuestionsController.php',
        'MasterStudy\\Lms\\Http\\Serializers\\AbstractSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/AbstractSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CertificateSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CertificateSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CommentSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CommentSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CourseCategorySerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CourseCategorySerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CourseLevelSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CourseLevelSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CourseSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CourseSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CurriculumMaterialSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CurriculumMaterialSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\CurriculumSectionSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/CurriculumSectionSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\PostSerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/PostSerializer.php',
        'MasterStudy\\Lms\\Http\\Serializers\\QuestionCategorySerializer' => __DIR__ . '/../..' . '/includes/Http/Serializers/QuestionCategorySerializer.php',
        'MasterStudy\\Lms\\Http\\WpResponseFactory' => __DIR__ . '/../..' . '/includes/Http/WpResponseFactory.php',
        'MasterStudy\\Lms\\Models\\Course' => __DIR__ . '/../..' . '/includes/Models/Course.php',
        'MasterStudy\\Lms\\Plugin' => __DIR__ . '/../..' . '/includes/Plugin.php',
        'MasterStudy\\Lms\\Plugin\\Addon' => __DIR__ . '/../..' . '/includes/Plugin/Addon.php',
        'MasterStudy\\Lms\\Plugin\\Addons' => __DIR__ . '/../..' . '/includes/Plugin/Addons.php',
        'MasterStudy\\Lms\\Plugin\\Media' => __DIR__ . '/../..' . '/includes/Plugin/Media.php',
        'MasterStudy\\Lms\\Plugin\\PostType' => __DIR__ . '/../..' . '/includes/Plugin/PostType.php',
        'MasterStudy\\Lms\\Plugin\\Taxonomy' => __DIR__ . '/../..' . '/includes/Plugin/Taxonomy.php',
        'MasterStudy\\Lms\\Repositories\\AbstractRepository' => __DIR__ . '/../..' . '/includes/Repositories/AbstractRepository.php',
        'MasterStudy\\Lms\\Repositories\\CertificateRepository' => __DIR__ . '/../..' . '/includes/Repositories/CertificateRepository.php',
        'MasterStudy\\Lms\\Repositories\\CourseCategoryRepository' => __DIR__ . '/../..' . '/includes/Repositories/CourseCategoryRepository.php',
        'MasterStudy\\Lms\\Repositories\\CourseRepository' => __DIR__ . '/../..' . '/includes/Repositories/CourseRepository.php',
        'MasterStudy\\Lms\\Repositories\\CurriculumMaterialRepository' => __DIR__ . '/../..' . '/includes/Repositories/CurriculumMaterialRepository.php',
        'MasterStudy\\Lms\\Repositories\\CurriculumRepository' => __DIR__ . '/../..' . '/includes/Repositories/CurriculumRepository.php',
        'MasterStudy\\Lms\\Repositories\\CurriculumSectionRepository' => __DIR__ . '/../..' . '/includes/Repositories/CurriculumSectionRepository.php',
        'MasterStudy\\Lms\\Repositories\\FaqRepository' => __DIR__ . '/../..' . '/includes/Repositories/FaqRepository.php',
        'MasterStudy\\Lms\\Repositories\\FileMaterialRepository' => __DIR__ . '/../..' . '/includes/Repositories/FileMaterialRepository.php',
        'MasterStudy\\Lms\\Repositories\\LessonRepository' => __DIR__ . '/../..' . '/includes/Repositories/LessonRepository.php',
        'MasterStudy\\Lms\\Repositories\\PricingRepository' => __DIR__ . '/../..' . '/includes/Repositories/PricingRepository.php',
        'MasterStudy\\Lms\\Repositories\\QuestionCategoryRepository' => __DIR__ . '/../..' . '/includes/Repositories/QuestionCategoryRepository.php',
        'MasterStudy\\Lms\\Repositories\\QuestionRepository' => __DIR__ . '/../..' . '/includes/Repositories/QuestionRepository.php',
        'MasterStudy\\Lms\\Repositories\\QuizRepository' => __DIR__ . '/../..' . '/includes/Repositories/QuizRepository.php',
        'MasterStudy\\Lms\\Rest\\SearchHandlers\\CourseSearchHandler' => __DIR__ . '/../..' . '/includes/Rest/SearchHandlers/CourseSearchHandler.php',
        'MasterStudy\\Lms\\Rest\\SearchHandlers\\QuestionsSearchHandler' => __DIR__ . '/../..' . '/includes/Rest/SearchHandlers/QuestionsSearchHandler.php',
        'MasterStudy\\Lms\\Routing\\MiddlewareInterface' => __DIR__ . '/../..' . '/includes/Routing/MiddlewareInterface.php',
        'MasterStudy\\Lms\\Routing\\Middleware\\Authentication' => __DIR__ . '/../..' . '/includes/Routing/Middleware/Authentication.php',
        'MasterStudy\\Lms\\Routing\\Middleware\\CoInstructor' => __DIR__ . '/../..' . '/includes/Routing/Middleware/CoInstructor.php',
        'MasterStudy\\Lms\\Routing\\Middleware\\CommentGuard' => __DIR__ . '/../..' . '/includes/Routing/Middleware/CommentGuard.php',
        'MasterStudy\\Lms\\Routing\\Middleware\\ConvertToWpResponse' => __DIR__ . '/../..' . '/includes/Routing/Middleware/ConvertToWpResponse.php',
        'MasterStudy\\Lms\\Routing\\Middleware\\Instructor' => __DIR__ . '/../..' . '/includes/Routing/Middleware/Instructor.php',
        'MasterStudy\\Lms\\Routing\\Route' => __DIR__ . '/../..' . '/includes/Routing/Route.php',
        'MasterStudy\\Lms\\Routing\\RouteCollection' => __DIR__ . '/../..' . '/includes/Routing/RouteCollection.php',
        'MasterStudy\\Lms\\Routing\\Router' => __DIR__ . '/../..' . '/includes/Routing/Router.php',
        'MasterStudy\\Lms\\Routing\\RouterPipeline' => __DIR__ . '/../..' . '/includes/Routing/RouterPipeline.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Field' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Field.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Addon' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Addon.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Category' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Category.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Comment' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Comment.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\CourseStatus' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/CourseStatus.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\CurriculumMaterial' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/CurriculumMaterial.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\CurriculumSection' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/CurriculumSection.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\DurationMeasure' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/DurationMeasure.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\FileMaterial' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/FileMaterial.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\LessonType' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/LessonType.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\LessonVideoType' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/LessonVideoType.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Level' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Level.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Media' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Media.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\Post' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/Post.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\PostStatus' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/PostStatus.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\QuestionCategory' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/QuestionCategory.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\QuestionType' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/QuestionType.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\QuestionView' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/QuestionView.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\QuizStyle' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/QuizStyle.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Fields\\User' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Fields/User.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\RequestInterface' => __DIR__ . '/../..' . '/includes/Routing/Swagger/RequestInterface.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\ResponseInterface' => __DIR__ . '/../..' . '/includes/Routing/Swagger/ResponseInterface.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Route' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Route.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Approve' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Approve.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Create' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Create.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Get' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Get.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Reply' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Reply.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Spam' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Spam.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Trash' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Trash.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Unapprove' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Unapprove.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Unspam' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Unspam.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Untrash' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Untrash.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Comment\\Update' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Comment/Update.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\CourseBuilder\\GetSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/CourseBuilder/GetSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\AddNew' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/AddNew.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Create' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Create.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\CreateCategory' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/CreateCategory.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\CreateMaterial' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/CreateMaterial.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\CreateSection' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/CreateSection.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\DeleteMaterial' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/DeleteMaterial.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\DeleteSection' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/DeleteSection.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\GetCurriculum' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/GetCurriculum.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\ImportMaterials' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/ImportMaterials.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\ImportSearch' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/ImportSearch.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\UpdateMaterial' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/UpdateMaterial.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Curriculum\\UpdateSection' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Curriculum/UpdateSection.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\Edit' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/Edit.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\GetAnnouncement' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/GetAnnouncement.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\GetFaqSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/GetFaqSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\GetPricingSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/GetPricingSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\GetSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/GetSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateAccessSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateAccessSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateAnnouncement' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateAnnouncement.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateCertificateSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateCertificateSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateFaqSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateFaqSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateFilesSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateFilesSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdatePricingSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdatePricingSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateSettings' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateSettings.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Course\\UpdateStatus' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Course/UpdateStatus.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\HealthCheck' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/HealthCheck.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Lesson\\Create' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Lesson/Create.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Lesson\\Get' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Lesson/Get.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Lesson\\Update' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Lesson/Update.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Media\\Delete' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Media/Delete.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Media\\Upload' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Media/Upload.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\Create' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/Create.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\CreateCategory' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/CreateCategory.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\Delete' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/Delete.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\Get' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/Get.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\GetCategories' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/GetCategories.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Question\\Update' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Question/Update.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Quiz\\Create' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Quiz/Create.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Quiz\\Delete' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Quiz/Delete.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Quiz\\Get' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Quiz/Get.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Quiz\\Update' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Quiz/Update.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Routes\\Quiz\\UpdateQuestions' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Routes/Quiz/UpdateQuestions.php',
        'MasterStudy\\Lms\\Routing\\Swagger\\Schema' => __DIR__ . '/../..' . '/includes/Routing/Swagger/Schema.php',
        'MasterStudy\\Lms\\Utility\\Question' => __DIR__ . '/../..' . '/includes/Utility/Question.php',
        'MasterStudy\\Lms\\Utility\\Sanitizer' => __DIR__ . '/../..' . '/includes/Utility/Sanitizer.php',
        'MasterStudy\\Lms\\Validation\\ConditionalRules' => __DIR__ . '/../..' . '/includes/Validation/ConditionalRules.php',
        'MasterStudy\\Lms\\Validation\\Validator' => __DIR__ . '/../..' . '/includes/Validation/Validator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit26b060b64247a2a6e91b00a4357fb1ca::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit26b060b64247a2a6e91b00a4357fb1ca::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit26b060b64247a2a6e91b00a4357fb1ca::$classMap;

        }, null, ClassLoader::class);
    }
}
