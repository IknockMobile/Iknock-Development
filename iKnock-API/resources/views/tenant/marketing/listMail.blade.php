
<style>
    /* Inline CSS for responsiveness */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    /* For small screens, make the table stack on top of each other */
    @media (max-width: 600px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }

        th {
            text-align: left;
        }

        tr {
            border-bottom: 1px solid #ddd;
        }
    }
</style>
<table>
<!--    <thead>
        <tr>
            <th width="15%">Homeowner Name</th>
            <th width="15%">Homeowner Address</th>
            <th width="15%">Lead</th>
            <th width="15%">Investor</th>
            <th width="20%">Notes and Actions</th>
            <th width="20%">Investor Notes</th>
        </tr>
    </thead>-->
    <tbody>
        @forelse($marketings as $key=>$marketing)
        <tr>
            <th width="20%">Homeowner Name</th>
            <td width="80%">{{ $marketing->title }}</td>
        </tr>
        <tr>
            <th width="20%">Homeowner Address</th>
            <td width="80%">{{ $marketing->address }}</td>
        </tr>
        <tr>
            <th width="20%">Lead</th>
            <td width="80%">{{$marketing->lead_first_name}} {{$marketing->lead_last_name}}</td>
        </tr>
        <tr>
            <th width="20%">Investor</th>
            <td width="80%">{{$marketing->in_first_name}} {{$marketing->in_last_name}}</td>
        </tr>
        <tr>
            <th width="20%">Notes and Actions</th>
            <td width="80%">{{$marketing->admin_notes}}</td>
        </tr>
        <tr>
            <th width="20%">Investor Notes</th>
            <td width="80%">{{$marketing->investore_note}}</td>
        </tr>            
        <tr>
            <th width="20%">&nbsp;</th>
            <td width="80%">&nbsp;</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align: center !important;">No Data Found!</td>
        </tr>
        @endforelse                        
    </tbody>
</table>




