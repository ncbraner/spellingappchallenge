<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spelling Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
@include('includes.header')

<div class="container mt-5">
    <div class="row">
        <div class="col-6">
            <h4>Spelling Test</h4>
        </div>
        <div class="col-6 text-right">
            <h4>Points: <span id="userPoints">{{ $points }}</span></h4>
        </div>
    </div>

    @if (count($words) > 0)
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Spelling Word</th>
                <th>Student Input</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @endif
            @forelse($words as $word)

                <tr>
                    <td>{{ $word }}</td>
                    <td><input type="text" class="form-control" id="input_{{ $loop->index }}"></td>
                    <td>
                        <button class="btn btn-primary" onclick="verify('{{ $word }}', 'input_{{ $loop->index }}')">
                            Verify
                        </button>
                    </td>
                </tr>

            @empty
                <p>You do not have any spelling words, Lets go to the word manager and add them!</p>
                <a href="{{route('word-manager')}}">Word Manager</a>
            @endforelse

            @if (count($words) > 0)
            </tbody>
        </table>
    @endif

</div>

<script>
    function verify(correctWord, inputId) {
        const inputValue = document.getElementById(inputId).value;

        if (inputValue === correctWord) {
            // Make an API call to update points
            fetch('/api/update-points', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Add any other headers like authentication headers if needed
                },
                body: JSON.stringify({
                    pointsToAdd: 10,
                    user_id: "{{ $user_id }}"  // passing the user_id blade variable
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const pointsElement = document.getElementById('userPoints');
                    pointsElement.textContent = data.totalPoints;
                    alert('Correct!');
                })
                .catch(error => {
                    console.error('Error updating points:', error);
                    alert('There was an error updating your points. Please try again.');
                });
        } else {
            alert('Incorrect. Try again.');
        }
    }
</script>

</body>
</html>
