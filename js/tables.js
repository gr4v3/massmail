/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var tables = {};

	tables.host = [
		{
		   header: "Hostname",
		   dataIndex: 'hostname',
		   dataType:'string',
		   width:200
		},
		{
		   header: "Status",
		   dataIndex: 'statusname',
		   dataType:'state',
		   dataOriginalIndex: 'status_id'
		},
		{
		   header: "Created",
		   dataIndex: 'created',
		   dataType:'date',
		   width:140
		}
	];


	tables.login = [
		{
		   header: "User Name",
		   dataIndex: 'name',
		   dataType:'string'
		},
		{
		   header: "User Email",
		   dataIndex: 'email',
		   dataType:'string'
		},
		{
		   header: "User Pass",
		   dataIndex: 'pass',
		   dataType:'string'
		},
		{
		   header: "IP Filter",
		   dataIndex: 'ip',
		   dataType:'string'
		},
		{
		   header: "Host Associated",
		   dataIndex: 'hostname',
		   dataType:'host',
		   dataOriginalIndex: 'host_id'
		},
		{
		   header: "Status",
		   dataIndex: 'statusname',
		   dataType:'state',
		   width:60,
		   dataOriginalIndex: 'status_id'
		},
		{
		   header: "Created",
		   dataIndex: 'created',
		   dataType:'date',
		   width:134
		}
	];



	tables.sent = [
		{
		   header: "Email Recipient",
		   dataIndex: 'email',
		   dataType:'string'
		},
		{
		   header: "Message ID",
		   dataIndex: 'track_key',
		   dataType:'string'
		},
		{
		   header: "bounce",
		   dataIndex: 'bounce',
		   dataType:'string'
		},
		{
		   header: "Status",
		   dataIndex: 'statusname',
		   dataType:'state'
		},
		{
		   header: "Sent",
		   dataIndex: 'created',
		   dataType:'date'
		}
	];
