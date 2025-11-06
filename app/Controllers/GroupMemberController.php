<?php
// controllers/GroupMemberController.php

namespace App\Controllers;

use PDO;
use App\Models\GroupMemberModel;
use App\Models\GroupModel;
use App\Models\StudentModel;

class GroupMemberController extends BaseController
{
    private GroupMemberModel $groupMemberModel;
    private GroupModel $groupModel;
    private StudentModel $studentModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->groupMemberModel = new GroupMemberModel($pdo);
        $this->groupModel = new GroupModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function index(): void
    {
        // Perhaps list all memberships
        $this->render('group_members/index');
    }

    public function add(int $groupId): void
    {
        $group = $this->groupModel->getFullGroup($groupId);
        $availableStudents = $this->studentModel->findAvailableStudents();
        $this->render('group_members/add', ['group' => $group, 'availableStudents' => $availableStudents]);
    }

    public function store(int $groupId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'] ?? 0;
            if ($this->groupMemberModel->addMember($groupId, $studentId)) {
                $this->redirect("groups/show/$groupId");
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to add member']);
    }

    public function show(int $groupId): void
    {
        $members = $this->groupMemberModel->getMembers($groupId);
        $group = $this->groupModel->getFullGroup($groupId);
        $this->render('group_members/show', ['members' => $members, 'group' => $group]);
    }

    public function destroy(int $groupId, int $studentId): void
    {
        if ($this->groupMemberModel->removeMember($groupId, $studentId)) {
            $this->redirect("groups/show/$groupId");
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to remove member']);
    }
}
