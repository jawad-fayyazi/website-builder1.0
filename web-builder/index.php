<?php
include('php/db_config.php');

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$categories_sql = "SELECT DISTINCT template_category FROM templates_data";
$categories_result = $conn->query($categories_sql);
$categories = [];
if ($categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['template_category'];
    }
}





if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_project_name'], $_POST['template_json'])) {
    $project_name = $conn->real_escape_string($_POST['new_project_name']);
    $template_json = $conn->real_escape_string($_POST['template_json']);

    // Check for duplicate project name
    $check_sql = "SELECT COUNT(*) AS count FROM projects_data WHERE project_name = '$project_name'";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "<script>alert('Sorry, this project name is already in use.');</script>";
    } else {
        $sql = "INSERT INTO projects_data (project_name, project_json) VALUES ('$project_name', '$template_json')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New project created successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}







// Handle new project creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_name'])) {
    $project_name = $conn->real_escape_string($_POST['project_name']);

    // Check if project name already exists
    $check_sql = "SELECT COUNT(*) AS count FROM projects_data WHERE project_name = '$project_name'";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "<script>alert('Sorry, this project name is already in use. Please choose a different name.');</script>";
    } else {
$project_json = json_encode([
    "assets" => [],
    "styles" => [],
    "pages" => [[
        "name" => "Page 1",
    ]],
    "symbols" => [],
    "dataSources" => []
]);
        // Insert without specifying project_id (auto-increment will handle it)
        $sql = "INSERT INTO projects_data (project_name, project_json) VALUES ('$project_name', '$project_json')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New project created successfully!');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Handle project deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Delete project from the database
    $delete_sql = "DELETE FROM projects_data WHERE project_id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Project deleted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "Error deleting project: " . $conn->error;
    }
}

// Fetch all projects
$sql = "SELECT project_id, project_name FROM projects_data";
$result = $conn->query($sql);
$projects = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main App Page</title>
    <link rel="stylesheet" href="css/index-style.css">
</head>

<body>

    <h1>Project Dashboard</h1>

    <div class="container">

        <!-- Project List -->
        <div class="project-list">
            <h2>Your Projects</h2>
            <?php if (!empty($projects)): ?>
                <ul>
                    <?php foreach ($projects as $project): ?>
                        <li class="project-item">
                            <div>
                                <strong><?php echo htmlspecialchars($project['project_name']); ?></strong> (ID:
                                <?php echo $project['project_id']; ?>)
                            </div>
                            <div>
                                <a href="web-builder.php?project_id=<?php echo $project['project_id']; ?>&project_name=<?php echo $project['project_name']; ?>" target="_blank">Open
                                    Project</a>
                                <!-- Delete Button -->
                                <a href="?delete_id=<?php echo $project['project_id']; ?>" class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No projects found. Create your first project!</p>
            <?php endif; ?>
        </div>

        <!-- Create New Project Form -->
        <div class="create-project">
            <h2>Create New Project</h2>
            <form method="POST" action="">
                <label for="project_name">Project Name:</label>
                <input pattern="[A-Za-z0-9 ]*" title="Only letters and numbers are allowed" type="text" id="project_name" name="project_name" required>
                <button type="submit">Create Project</button>
            </form>
        </div>


        <div class="template-section">
    <h2>Template Categories</h2>
    <div class="categories-container">
        <?php foreach ($categories as $category): ?>
                    <div class="category-card" onclick="showTemplates('<?php echo $category; ?>')">
                        <h3><?php echo htmlspecialchars($category); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Template Modal -->
        <div id="templateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modalCategory"></h2>
                <div id="templatesContainer" class="templates-container"></div>
            </div>
        </div>
        
        <!-- Project Modal -->
        <div id="projectModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="selectedTemplate"></h2>
                <form method="POST" action="">
                    <label for="new_project_name">Project Name:</label>
                    <input pattern="[A-Za-z0-9 ]*" title="Only letters and numbers are allowed" type="text" id="new_project_name" name="new_project_name" required>
                    <input type="hidden" id="template_json" name="template_json">
                    <button type="submit">Create Project</button>
                </form>
            </div>
        </div>
    </div>
<script src="js/index.js"></script>
</body>

</html>