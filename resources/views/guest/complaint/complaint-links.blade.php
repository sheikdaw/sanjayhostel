{{-- resources/views/admin/complaint-links.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Portal Links</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Complaint Portal Links</h1>
        <p class="text-gray-600 mb-6">Each hostel has its own unique complaint portal link.</p>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Hostel</th>
                        <th class="px-4 py-3 text-left">Complaint Portal Link</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hostels as $hostel)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-semibold">{{ $hostel->name }}</td>
                        <td class="px-4 py-3">
                            <input type="text" readonly value="{{ $encodedLinks[$hostel->id] }}"
                                id="link-{{ $hostel->id }}"
                                class="w-full text-xs border rounded px-2 py-1 bg-gray-50">
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <button onclick="copyLink('link-{{ $hostel->id }}')"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Copy
                            </button>
                            <a href="{{ $encodedLinks[$hostel->id] }}" target="_blank"
                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 ml-1">
                                Open
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<script>
function copyLink(id) {
    const el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
    alert('Link copied!');
}
</script>
</body>
</html>
