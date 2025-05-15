<?php
// Include database connection
include 'includes/connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission for adding new department
if (isset($_POST['add_department'])) {
    $department_name = $_POST['department_name'];
    $department_chair_id = $_POST['department_chair'];
    $department_desc = $_POST['department_desc'];
    
    // Get the chair's full name from admin table
    $query = "SELECT CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name FROM admin WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_chair_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $department_chair_name = "Sir/Ma'am. " . $row['full_name'];
    
    // Insert new department
    $insert_query = "INSERT INTO department (department, department_chair, department_id, department_desc) 
                    VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssis", $department_name, $department_chair_name, $department_chair_id, $department_desc);
    
    if ($stmt->execute()) {
        $success_message = "Department added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Fetch all departments
$query = "SELECT d.*, a.first_name, a.middle_name, a.last_name 
          FROM department d 
          LEFT JOIN admin a ON d.department_id = a.id";
$result = $conn->query($query);
$departments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Fetch all admins for dropdown
$query = "SELECT id, first_name, middle_name, last_name FROM admin";
$admin_result = $conn->query($query);
$admins = [];
if ($admin_result->num_rows > 0) {
    while ($row = $admin_result->fetch_assoc()) {
        $admins[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Departments</h1>
        <button id="openModalBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add Department
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Departments Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Chair</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($departments as $dept): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $dept['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $dept['department']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $dept['department_chair']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $dept['department_desc']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button class="text-blue-500 hover:text-blue-700 mr-2 edit-btn" data-id="<?php echo $dept['id']; ?>">Edit</button>
                        <button class="text-red-500 hover:text-red-700 delete-btn" data-id="<?php echo $dept['id']; ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($departments)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center">No departments found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Department Modal -->
    <div id="departmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add New Department</h2>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="department_name" class="block text-gray-700 text-sm font-bold mb-2">Department Name:</label>
                    <input type="text" id="department_name" name="department_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                
                <div class="mb-4">
                    <label for="department_chair" class="block text-gray-700 text-sm font-bold mb-2">Department Chair:</label>
                    <select id="department_chair" name="department_chair" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select Department Chair</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?php echo $admin['id']; ?>">
                                <?php echo $admin['first_name'] . ' ' . $admin['middle_name'] . ' ' . $admin['last_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="department_desc" class="block text-gray-700 text-sm font-bold mb-2">Department Description:</label>
                    <textarea id="department_desc" name="department_desc" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3" required></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Cancel
                    </button>
                    <button type="submit" name="add_department" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal functionality
    const modal = document.getElementById('departmentModal');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    openModalBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    closeModalBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    // Edit and Delete functionality can be added here
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const departmentId = button.getAttribute('data-id');
            // Implement edit functionality
            alert('Edit department with ID: ' + departmentId);
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const departmentId = button.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this department?')) {
                // Implement delete functionality
                window.location.href = `manage-departments.php?delete=${departmentId}`;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>