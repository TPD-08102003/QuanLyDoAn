<?php
// controllers/HomeController.php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ClassesModel;
use App\Models\FacultiesModel;
use App\Models\StudentModel;

use PDO;
use Exception;

class HomeController extends BaseController
{
    private ProjectModel $projectModel;
    private ClassesModel $classesModel;
    private FacultiesModel $facultiesModel;
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        // Khởi tạo ProjectModel để lấy dữ liệu
        $this->projectModel = new ProjectModel($pdo);
        $this->classesModel = new ClassesModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function index(): void
    {
        // try {
        //     // 1. Lấy dữ liệu thống kê
        //     $totalFaculties = $this->facultiesModel->getTotalFaculties();
        //     $totalClasses = $this->classesModel->getTotalClasses();
        //     $totalStudents = $this->studentModel->getTotalStudents();
        //     $totalProjects = $this->projectModel->getTotalProjects();

        //     $statistics = [
        //         'faculties' => $totalFaculties,
        //         'classes' => $totalClasses,
        //         'students' => $totalStudents,
        //         'projects' => $totalProjects,
        //     ];

        //     // 2. Lấy 3 đồ án mới nhất cho Carousel
        //     $latestProjects = $this->projectModel->getLatestProjects(3);

        //     // 3. Lấy TẤT CẢ đồ án cho phần hiển thị dạng card
        //     $allProjects = $this->projectModel->getAllProjectsWithDetails();

        //     // Truyền tất cả dữ liệu vào view
        //     $this->render('home/index', [
        //         'title' => 'Trang Chủ - Quản lý Đồ án',
        //         'statistics' => $statistics,
        //         'latestProjects' => $latestProjects, // Dữ liệu cho carousel (3 items)
        //         'allProjects' => $allProjects,       // Dữ liệu cho cards (tất cả)
        //     ]);
        // } catch (Exception $e) {
        //     // Xử lý lỗi hệ thống thân thiện
        //     error_log($e->getMessage());
        //     // $this->render('home/index');
        //     $this->render('home/index', [
        //         'title' => 'Lỗi Tải Trang',
        //         'error' => 'Không thể tải dữ liệu trang chủ.',
        //         'statistics' => ['faculties' => 0, 'classes' => 0, 'students' => 0, 'projects' => 0],
        //         'latestProjects' => [],
        //         'allProjects' => []
        //     ]);
        // }
        try {
            // 1. Lấy dữ liệu thống kê
            $totalFaculties = $this->facultiesModel->getTotalFaculties();
            $totalClasses = $this->classesModel->getTotalClasses();
            $totalStudents = $this->studentModel->getTotalStudents();
            $totalProjects = $this->projectModel->getTotalProjects();

            $statistics = [
                'faculties' => $totalFaculties,
                'classes' => $totalClasses,
                'students' => $totalStudents,
                'projects' => $totalProjects,
            ];

            // 2. Lấy 4 đồ án mới nhất cho Carousel
            $latestProjects = $this->projectModel->getLatestProjects(4);

            // 3. Lấy TẤT CẢ đồ án có chi tiết để hiển thị dạng card trên trang chủ
            $allProjects = $this->projectModel->getAllProjectsWithDetails();

            // Truyền tất cả dữ liệu vào view
            $this->render('home/index', [
                'title' => 'Trang Chủ - Quản lý Đồ án',
                'statistics' => $statistics,
                'latestProjects' => $latestProjects, // Dữ liệu cho carousel (4 items)
                'allProjects' => $allProjects,       // Dữ liệu cho cards (tất cả đồ án)
            ]);
        } catch (Exception $e) {
            // Xử lý lỗi hệ thống thân thiện
            error_log($e->getMessage());
            $this->render('home/index', [
                'title' => 'Lỗi Tải Trang',
                'error' => 'Không thể tải dữ liệu trang chủ.',
                'statistics' => ['faculties' => 0, 'classes' => 0, 'students' => 0, 'projects' => 0],
                'latestProjects' => [],
                'allProjects' => [],
            ]);
        }
    }

    /**
     * Hiển thị trang Giới thiệu
     * URL: /quanlydoan/home/about (hoặc /quanlydoan/about tùy router)
     */
    public function about(): void
    {
        $this->render('home/about', [
            'title' => 'Giới thiệu - Quản lý Đồ án'
        ]);
    }

    /**
     * Hiển thị trang Liên hệ
     * URL: /quanlydoan/home/contact (hoặc /quanlydoan/contact tùy router)
     */
    public function contact(): void
    {
        $this->render('home/contact', [
            'title' => 'Liên hệ - Quản lý Đồ án'
        ]);
    }
}
