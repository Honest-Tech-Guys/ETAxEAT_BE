@php
    $fieldName = $row->field;
    $structuredHours = [];

    if (old($fieldName)) {
        $oldData = old($fieldName);
        if (isset($oldData['day']) && is_array($oldData['day'])) {
            for ($i = 0; $i < count($oldData['day']); $i++) {
                $day = $oldData['day'][$i];
                if (!isset($structuredHours[$day])) {
                    $structuredHours[$day] = [];
                }
                $structuredHours[$day][] = [
                    'open' => $oldData['open'][$i] ?? '',
                    'close' => $oldData['close'][$i] ?? '',
                ];
            }
        }
    } else {
        $rawDbData = $dataTypeContent->getRawOriginal($fieldName) ?? '[]';
        $structuredHours = json_decode($rawDbData, true);
    }
    
    if (!is_array($structuredHours)) {
        $structuredHours = [];
    }
@endphp

<div>
    <table class="table table-bordered" id="operating-hours-table">
        <thead>
            <tr>
                <th>Day of Week</th>
                <th>Open Time</th>
                <th>Close Time</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($structuredHours))
                @foreach($structuredHours as $day => $slots)
                    @if(is_array($slots))
                        @foreach($slots as $slot)
                            <tr>
                                <td>
                                    <select class="form-control" name="{{ $fieldName }}[day][]">
                                        <option value="monday" @if($day == 'monday') selected @endif>Monday</option>
                                        <option value="tuesday" @if($day == 'tuesday') selected @endif>Tuesday</option>
                                        <option value="wednesday" @if($day == 'wednesday') selected @endif>Wednesday</option>
                                        <option value="thursday" @if($day == 'thursday') selected @endif>Thursday</option>
                                        <option value="friday" @if($day == 'friday') selected @endif>Friday</option>
                                        <option value="saturday" @if($day == 'saturday') selected @endif>Saturday</option>
                                        <option value="sunday" @if($day == 'sunday') selected @endif>Sunday</option>
                                    </select>
                                </td>
                                <td><input type="time" class="form-control" name="{{ $fieldName }}[open][]" value="{{ $slot['open'] ?? '' }}"></td>
                                <td><input type="time" class="form-control" name="{{ $fieldName }}[close][]" value="{{ $slot['close'] ?? '' }}"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>
    <button type="button" class="btn btn-success" id="add-row">Add Time Slot</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.querySelector('#operating-hours-table tbody');
        const addRowButton = document.getElementById('add-row');

        function createRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="form-control" name="{{ $fieldName }}[day][]">
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </td>
                <td><input type="time" class="form-control" name="{{ $fieldName }}[open][]"></td>
                <td><input type="time" class="form-control" name="{{ $fieldName }}[close][]"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
            `;
            tableBody.appendChild(row);
        }

        addRowButton.addEventListener('click', createRow);

        tableBody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>