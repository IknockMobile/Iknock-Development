<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;

class AuditController extends Controller {

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function index(Request $request) {
        $audits = Audit::latest();

        if ($request->has('search_id') && !empty($request->search_id)) {
            $audits = $audits->where('auditable_id', $request->search_id);
        }

        if ($request->has('search_text') && !empty($request->search_text)) {
            $audits = $audits->Where('old_values', 'like', '%' . $request->search_text . '%')
                    ->orWhere('new_values', 'like', '%' . $request->search_text . '%')
                    ->orWhere('user', 'like', '%' . $request->search_text . '%')
//                    ->orWhere('ip_address', 'like', '%' . $request->search_text . '%')
//                    ->orWhere('auditable_type', 'like', '%' . $request->search_text . '%')
//                    ->orWhere('url', 'like', '%' . $request->search_text . '%')
//                    ->orWhere('ip_address', 'like', '%' . $request->search_text . '%')
//                    ->orWhere('user_agent', 'like', '%' . $request->search_text . '%')
                    ;
        }

        if ($request->has('event') && !empty($request->event)) {
            $audits = $audits->where('event', $request->event);
        }

        if ($request->has('audit_type') && !empty($request->audit_type)) {
            $audits = $audits->where('auditable_type', $request->audit_type);
        }

        $audits = $audits->paginate(15);

        $auditTypes = Audit::latest()->groupBy('auditable_type')->pluck('auditable_type')->toArray();

        return view('tenant.audit.index', compact('audits', 'auditTypes'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function show(Audit $audit) {
        return view('tenant.audit.show', compact('audit'));
    }

}
