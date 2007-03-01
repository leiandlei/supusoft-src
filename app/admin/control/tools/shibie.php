<?php
$sql = "select p.audit_type,p.cti_code,p.iso,p.audit_ver,p.audit_code as pcode,cti.audit_code as cticode,p.mark as pmark,cti.mark as ctimark,p.exc_clauses_new as pexc,cti.exc_clauses as ctiexc,p.scope as pscope,cti.scope as ctiscope 
from sp_project p 
left join sp_contract_item cti on p.cti_id=cti.cti_id where p.deleted=0 and cti.deleted=0 and p.scope!=cti.scope order by p.id";
echo '<pre />';
print_r($sql);exit;
