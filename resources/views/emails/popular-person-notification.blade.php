<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Popular Persons Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #e91e63;
            border-bottom: 2px solid #e91e63;
            padding-bottom: 10px;
        }
        .alert {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #e91e63;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>🔥 Popular Persons Alert</h1>
    
    <div class="alert">
        <strong>Notification:</strong> The following persons have received more than 50 likes and may require attention.
    </div>

    <p>Dear Admin,</p>
    
    <p>This is an automated notification to inform you that <strong>{{ $popularPersons->count() }}</strong> person(s) have been liked by more than 50 users.</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Location</th>
                <th>Likes Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($popularPersons as $person)
            <tr>
                <td>{{ $person->id }}</td>
                <td>{{ $person->name }}</td>
                <td>{{ $person->age }}</td>
                <td>{{ $person->location }}</td>
                <td><strong>{{ $person->likes_count }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>Please review these profiles to ensure they meet our platform standards.</p>

    <div class="footer">
        <p>This is an automated email from Tinder API System.</p>
        <p>Generated at: {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
