<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Manager</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function updateWordList(data) {
            const wordList = document.querySelector('.list-group');
            wordList.innerHTML = '';
            Object.values(data.currentWords).forEach(word => {
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item');
                listItem.innerHTML = `<input type="checkbox" name="selected_words[]" value="${word}"> ${word}`;
                wordList.appendChild(listItem);
            });
        }

        window.onload = function () {
            // Handle remove-words form
            document.querySelector('form[action="/api/remove-words"]').addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const token = localStorage.getItem('token'); // Get the token from local storage

                fetch('/api/remove-words', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Authorization': `Bearer ${token}` // Add the bearer token to the headers

                    }
                })
                    .then(response => response.json())
                    .then(updateWordList)
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Handle add-words form
            document.querySelector('form[action="/api/add-words"]').addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const token = localStorage.getItem('token'); // Get the token from local storage

                fetch('/api/add-words', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json', // Tell the server we want JSON back
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Authorization': `Bearer ${token}` // Add the bearer token to the headers
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        updateWordList(data);
                        this.reset(); // Reset the form fields
                    }).catch(error => {
                    console.error('Error:', error);
                });
            });

        }
    </script>
</head>
<body>

@include('includes.header')


<div class="container mt-5">
    <h2>Word Manager</h2>


    <!-- Form to add new words -->
    <div class="mt-4">
        <h1>Add New Words</h1>
        <form action="/api/add-words" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user_id }}">
            @for($i = 0; $i < 20; $i++)
                <div class="form-group">
                    <input type="text" class="form-control" name="words[]" placeholder="Word {{ $i + 1 }}">
                </div>
            @endfor
            <button type="submit" class="btn btn-primary">Add Words</button>
        </form>
    </div>

    <!-- List of current words with checkboxes -->
    <div class="mt-5">
        <h1>Remove old words</h1>
        <form action="/api/remove-words" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user_id }}">
            <ul class="list-group">
                @foreach($currentWords as $word)
                    <li class="list-group-item">
                        <input type="checkbox" name="selected_words[]" value="{{ $word }}"> {{ $word }}
                    </li>
                @endforeach
            </ul>
            <button type="submit" class="btn btn-secondary mt-3">Remove Selected Words</button>
        </form>
    </div>
</div>

</body>
</html>
