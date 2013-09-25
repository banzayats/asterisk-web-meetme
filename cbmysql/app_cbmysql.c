/* Asterisk -- A telephony toolkit for Linux.
 *
 * Conference Scheduling 
 * 
 * Copyright (C) 1999, Mark Spencer
 * Copyright (c) 2006, Dan Austin
 *
 * Mark Spencer <markster@linux-support.net>
 * Dan Austin <dan_austin@fitawi.com>
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License
 */


/*** MODULEINFO
	<depend>mysqlclient</depend>
***/


#include "asterisk.h"


#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <pthread.h>
#include <mysql/mysql.h>
#include <mysql/errmsg.h>

#include "asterisk/autoconfig.h"
#include "asterisk/lock.h"
#include "asterisk/file.h"
#include "asterisk/cli.h"
#include "asterisk/config.h"
#include "asterisk/logger.h"
#include "asterisk/channel.h"
#include "asterisk/pbx.h"
#include "asterisk/module.h"
#include "asterisk/app.h"
#include "asterisk/say.h"
#include "asterisk/options.h"

#define AST_MODULE "cbmysql"
#define DATE_FORMAT "%Y-%m-%d %H:%M:%S"
#define CBMYSQL_CONFIG "cbmysql.conf"

static char *tdesc = "Conference Bridge MySQL";
static char *app = "CBMySQL";
static char *synopsis ="Conference Bridge application using MeetMe and MySQL\n";
static char *hostname = NULL, *dbname = NULL, *dbuser = NULL, *table = NULL, *password = NULL, *dbsock = NULL, *OptsAdm = NULL, *OptsUsr = NULL, *ConfApp = NULL, *ConfAppCount = NULL;
static int hostname_alloc = 0, dbname_alloc = 0, dbuser_alloc = 0, table_alloc = 0, password_alloc = 0, dbsock_alloc = 0;
static int dbport = 0;
static int connected = 0;
static time_t connect_time = 0;
static int records = 0;
static int totalrecords = 0;
static int DBOpts = 0;
static int earlyalert = 0;
static int fuzzystart = 0;


static struct handle{
	ast_mutex_t lock;
	MYSQL mysql;
} handle;


struct roomdetails{
	char roomtype[16];
	char roomno[30];
	char roompass[30];
	char silpass[30];
	char maxusers[30];
	char status[30];
	char aFlags[8];
	char uFlags[8];
	char bookid[10];
};

typedef struct userkeyin{
	char inroom[30];
	char inpass[30];
} userkeyin;

/* struct roomdetails dtmfmatch; */

/* prototypes.  Belong in a header somewhere? */
int getConf(struct ast_channel *chan, struct userkeyin dtmfinput, struct roomdetails *dtmfmatch);
int getPass(struct ast_channel *chan, struct userkeyin dtmfinput, struct roomdetails *dtmfmatch);

static int checkMax(struct ast_channel *chan, struct roomdetails *dtmfmatch)
{
	int res=0;
    	struct ast_app *app;
	char getvar[30]="AstVar";
	char roomno[30]="";
	char maxusers[30]="";
	char currentusers[30]="";
	char prompt[50]="";
	char arg[255]="";

    	strcpy(maxusers,dtmfmatch->maxusers);
	strcpy(roomno,dtmfmatch->roomno);
    	strcat(arg,roomno);
    	strcat(arg,",");
	strcat(arg,getvar);

	if(!(app = pbx_findapp(ConfAppCount))) {
		ast_log(LOG_WARNING, "Invalid participant count application: %s\n", ConfAppCount);
		return -1;
	}
 	res = pbx_exec(chan, app, arg);
   	strcpy(currentusers, pbx_builtin_getvar_helper(chan, getvar));
	if(atoi(currentusers) < atoi(maxusers)){
	ast_log(LOG_NOTICE, "Currentusers: %i\n", atoi(currentusers));
		if(atoi(currentusers)>0){
			strcpy(prompt,"conf-thereare");
			if (!ast_streamfile(chan, "conf-thereare", chan->language)){
				res = ast_waitstream(chan,"");
				ast_stopstream(chan);
				
				if (!ast_say_number(chan, (atoi(currentusers)), AST_DIGIT_ANY, chan->language, (char *) NULL)){
					res = ast_waitstream(chan,"");
					ast_stopstream(chan);
				}
				if (!ast_streamfile(chan, "conf-otherinparty", chan->language)){
					res = ast_waitstream(chan,"");
					ast_stopstream(chan);
				}
			}
		}
		res=1;
	}
	else{
        	ast_log(LOG_NOTICE, "Maximum users exeeded, Current users in room: %s\n",currentusers);
        	strcpy(prompt, "conf-locked");
        	if (!ast_streamfile(chan, prompt, chan->language)){
        		res = ast_waitstream(chan,"");
			ast_stopstream(chan);
		}
		res=-1;
	}
	return res;
}

static int passQuery(struct userkeyin dtmfinput, struct roomdetails *dtmfmatch)
{
	int res=0;
	char inroom[30]="";
	char inpass[30]="";

	strcpy(inroom,dtmfinput.inroom);
	strcpy(inpass,dtmfinput.inpass);

	ast_log(LOG_NOTICE, "Admin flags: %s\n", dtmfmatch->aFlags);
	ast_log(LOG_NOTICE, "user flags: %s\n", dtmfmatch->uFlags);

	if(!strcmp(dtmfmatch->silpass,inpass) && strlen(dtmfmatch->silpass) != 0){
		if (DBOpts) {
 		    strcpy(dtmfmatch->roomtype, dtmfmatch->aFlags);
		} else {
		   strcpy(dtmfmatch->roomtype, OptsAdm); 
		}
		res = 1;
	}
	else if(!strcmp(dtmfmatch->roompass,inpass)){
		   if (DBOpts) {
			strcpy(dtmfmatch->roomtype, dtmfmatch->uFlags);
		   } else {
			strcpy(dtmfmatch->roomtype, OptsUsr);
		   }
		res = 1;
	}
	else{
		ast_log(LOG_NOTICE, "CBMySQL: Invalid room or pass\n");		
                res = -1;
	}
	ast_log(LOG_NOTICE, "PASSQUERY: %s\n", dtmfinput.inroom);
	return res;
}

static int roomQuery(struct userkeyin dtmfinput, struct roomdetails *dtmfmatch)
{
	int rows = 0, res = 0, retries = 5;
	char sqlcmd[2048]="";
	char currenttime[128]="";
	char eatime[128]="";
	struct tm *l_time;
	time_t now;
	MYSQL_RES *result;
	MYSQL_ROW data;


	memset(sqlcmd,0,2048);

db_reconnect:
	if ((!connected) && (hostname || dbsock) && dbuser && password && dbname) {
		ast_mutex_lock(&handle.lock);		
		mysql_init(&handle.mysql);
		if (mysql_real_connect(&handle.mysql, hostname, dbuser, password, dbname, dbport, dbsock, 0)) {
			connected = 1;
			connect_time = time(NULL);
			records = 0;
		} else {
			ast_log(LOG_ERROR, "CBMySQL: cannot connect to database server %s/%s/%s/%s\n", hostname, dbname, dbuser, password);
		}
		ast_mutex_unlock(&handle.lock);		
	} else {
		int error;
		if ((error = mysql_ping(&handle.mysql))) {
			connected = 0;
			records = 0;
			switch (error) {
				case CR_SERVER_GONE_ERROR:
					ast_log(LOG_ERROR, "CBMySQL: server got error\n");
					break;
				default:
					ast_log(LOG_ERROR, "CBMySQL: Unknown connection error\n");
			}
		}
	}

	retries--;
	if (retries && !connected){
		usleep(20000);
		goto db_reconnect;
	}

	if (!connected) {
		ast_log(LOG_ERROR, "CBMySQL:  Retried connecting to the database five times, giving up.\n");
		return 0;
		
	} else {
   		time(&now);
   		
		l_time = localtime(&now);	
   	    	strftime(currenttime, sizeof(currenttime), DATE_FORMAT, l_time);


		sprintf(sqlcmd,"SELECT b.roomNo,b.roomPass,b.maxUser,b.status,b.silPass,b.aFlags,b.uFlags,b.bookId,b.startTime FROM %s b WHERE b.roomNo = '%s' AND b.status = 'A' AND b.startTime <= '%s' AND (b.endTime >= '%s' OR b.endTime = '0000-00-00 00:00:00')", table, dtmfinput.inroom, currenttime, currenttime);
		ast_mutex_lock(&handle.lock);
		mysql_real_query(&handle.mysql,sqlcmd,strlen(sqlcmd));    
		result = mysql_store_result(&handle.mysql);
		ast_mutex_unlock(&handle.lock);
		if ( result != NULL ){
			data = mysql_fetch_row(result);
			rows = mysql_num_rows(result);
			if (rows == 1){
				strcpy(dtmfmatch->roomno, data[0]);
				strcpy(dtmfmatch->roompass, data[1]);
				strcpy(dtmfmatch->maxusers, data[2]);
				strcpy(dtmfmatch->status, data[3]);
				strcpy(dtmfmatch->silpass, data[4]);
				strcpy(dtmfmatch->aFlags, data[5]);
				strcpy(dtmfmatch->uFlags, data[6]);
				strcpy(dtmfmatch->bookid, data[7]);
				if ( strlen(dtmfmatch->roompass) ==0 && strlen(dtmfmatch->silpass) ==0){
					res = 2;
				}
				else 
				{
					res =  1;
				}

				mysql_free_result(result);
				return res;
			}
		}
		else 
		{
			ast_log(LOG_ERROR,"CBMySQL: MySQL Error response: %s\n", mysql_error(&handle.mysql));
			return  0;
		}
				
		if (fuzzystart){
			now += fuzzystart;
			l_time = localtime(&now);	
   	    		strftime(currenttime, sizeof(currenttime), DATE_FORMAT, l_time);
		}


		sprintf(sqlcmd,"SELECT b.roomNo,b.roomPass,b.maxUser,b.status,b.silPass,b.aFlags,b.uFlags,b.bookId,b.startTime FROM %s b WHERE b.roomNo = '%s' AND b.status = 'A' AND b.startTime <= '%s' AND (b.endTime >= '%s' OR b.endTime = '0000-00-00 00:00:00')", table, dtmfinput.inroom, currenttime, currenttime);
		ast_mutex_lock(&handle.lock);
 		mysql_real_query(&handle.mysql,sqlcmd,strlen(sqlcmd));    
  		result = mysql_store_result(&handle.mysql);
		ast_mutex_unlock(&handle.lock);
		if ( result != NULL ){
 			data = mysql_fetch_row(result);
			rows = mysql_num_rows(result);
			if (rows == 1){
				strcpy(dtmfmatch->roomno, data[0]);
				strcpy(dtmfmatch->roompass, data[1]);
				strcpy(dtmfmatch->maxusers, data[2]);
				strcpy(dtmfmatch->status, data[3]);
				strcpy(dtmfmatch->silpass, data[4]);
				strcpy(dtmfmatch->aFlags, data[5]);
				strcpy(dtmfmatch->uFlags, data[6]);
				strcpy(dtmfmatch->bookid, data[7]);
				if ( strlen(dtmfmatch->roompass) ==0 && strlen(dtmfmatch->silpass) ==0){
					res = 2;
				}
				else 
				{
					res =  1;
				}

				mysql_free_result(result);
				return res;
			}
		}
		else
		{
			ast_log(LOG_ERROR,"CBMySQL: MySQL Error response: %s\n", mysql_error(&handle.mysql));
			return 0;
		}

		if (earlyalert){
			now += earlyalert;
			l_time = localtime(&now);	
   	    		strftime(currenttime, sizeof(currenttime), DATE_FORMAT, l_time);
		}

		sprintf(sqlcmd,"SELECT b.roomNo,b.roomPass,b.maxUser,b.status,b.silPass,b.aFlags,b.uFlags,b.bookId,b.startTime FROM %s b WHERE b.roomNo = '%s' AND b.status = 'A' AND b.startTime <= '%s' AND (b.endTime >= '%s' OR b.endTime = '0000-00-00 00:00:00')", table, dtmfinput.inroom, currenttime, currenttime);
		ast_mutex_lock(&handle.lock);
 		mysql_real_query(&handle.mysql,sqlcmd,strlen(sqlcmd));    
  		result = mysql_store_result(&handle.mysql);
		ast_mutex_unlock(&handle.lock);
		if ( result != NULL ){
 			data = mysql_fetch_row(result);
			rows = mysql_num_rows(result);
			if (rows == 1){
				ast_log(LOG_NOTICE,"CBMySQL: Caller attempted to join a conference that has not started.\n");
				mysql_free_result(result);
				return -2;
			}
		}
		else
		{
			ast_log(LOG_ERROR,"CBMySQL: MySQL Error response: %s\n", mysql_error(&handle.mysql));
			return 0;
		}
	}
	return 0;
}	

static int enterConf(struct ast_channel *chan, struct roomdetails *dtmfmatch)
{
        int res=0;
        /* char *prompt; */
	char prompt[50]="";
        struct ast_app *app;
	char arg2[255]="";
	char roomno2[80]="";
	char roomtype2[30]="";
	char recordfile[100]="conf-recordings/";

        strcpy(roomno2,dtmfmatch->roomno);
	strcpy(roomtype2,dtmfmatch->roomtype);
	strcpy(arg2, roomno2);

	strcat(arg2, ",");
	strcat(arg2, roomtype2);
	strcat(arg2, ",");

	strcat(recordfile, dtmfmatch->roomno);
	strcat(recordfile, "-");
	strcat(recordfile, dtmfmatch->bookid);

        ast_log(LOG_NOTICE, "Roomtype: %s\n", arg2);
	pbx_builtin_setvar_helper(chan, "MEETME_RECORDINGFILE", recordfile);
  
	if(strchr(dtmfmatch->aFlags, 'r'))
	{
		strcpy(prompt,"conf-call-recorded");
		if (!ast_streamfile(chan, prompt, chan->language)){
			res = ast_waitstream(chan,"");
			ast_stopstream(chan);
		}
	}
        if(!(app=pbx_findapp(ConfApp))){
		ast_log(LOG_WARNING, "Invalid conferencing application: %s\n", ConfApp);
		return -1;
	}
        res = pbx_exec(chan, app, arg2);

	return 1;
}

int getConf(struct ast_channel *chan, struct userkeyin dtmfinput, struct roomdetails *dtmfmatch)
{
	char *prompt;
	/* char *invalid;*/

	int res=0, retry=0, skip=0;

	if (ast_strlen_zero(dtmfinput.inroom)){
		strcpy(dtmfinput.inroom, "");
	} else {
		skip = 1;	
		retry = 2;
	}
	
	 while (retry++ < 3){ 
		if(!skip){
			prompt = "conf-getconfno";
        		if(ast_app_getdata(chan, prompt, dtmfinput.inroom, sizeof(dtmfinput.inroom) - 2, 0) < 0)
				return -1;
			res = ast_waitstream(chan,"");
		}
 		res = roomQuery(dtmfinput, dtmfmatch);
		if(res==1 || res==2)
			break;
		if(res == -2){
			prompt = "conf-has-not-started";
			if (!ast_streamfile(chan, prompt, chan->language)){
				res = ast_waitstream(chan,"");
				ast_stopstream(chan);
			}
			res = -1;
			break;
		}
		prompt = "conf-invalid";
		if (!ast_streamfile(chan, prompt, chan->language)){
			res = ast_waitstream(chan,"");
			ast_stopstream(chan);
		}
	}

	if(retry>=2 && (res < 1 && !skip)){
		prompt = "vm-goodbye";
		if (!ast_streamfile(chan, prompt, chan->language)){
			res = ast_waitstream(chan,"");
			ast_stopstream(chan);
		}
		res = -1;
	}
	return res;
}



int getPass(struct ast_channel *chan, struct userkeyin dtmfinput, struct roomdetails *dtmfmatch)
{
	char *prompt;
	/* char *invalid; */
	int res = 0, retry=0;

        prompt = "agent-pass";
        if(ast_app_getdata(chan, prompt, dtmfinput.inpass, sizeof(dtmfinput.inpass) - 2, 0) < 0)
		return -1;
        res = ast_waitstream(chan,"");
        res = passQuery(dtmfinput, dtmfmatch);

        while (retry++ < 2 && res !=1){
		    prompt = "auth-incorrect";
		    if(ast_app_getdata(chan, prompt, dtmfinput.inpass, sizeof(dtmfinput.inpass) - 2, 0) < 0)
			return -1;
		    res = ast_waitstream(chan,"");
			res = passQuery(dtmfinput, dtmfmatch);
				if(res==1)
					break;
		}

       if(retry>1 && res != 1){
			prompt = "vm-goodbye";
			if (!ast_streamfile(chan, prompt, chan->language)){
				res = ast_waitstream(chan,"");
				ast_stopstream(chan);
			}
			res = -1;
        }
	return res;
}

static int cb_exec(struct ast_channel *chan, void *data)
{
	int res=0, i=0, pos=0, optLen=0;
	struct userkeyin dtmfinput;
	struct roomdetails dtmfmatch;
	char confno[AST_MAX_EXTENSION] = "";
	char *notdata, *info;

	if (ast_strlen_zero(data)){
		notdata = "";
	} else {
		notdata = data;
	}
	
	info = ast_strdupa(notdata);

	if (info){
		char *tmp = strsep(&info, ",");
		ast_copy_string(confno, tmp, sizeof(confno));
	}

	if (!ast_strlen_zero(confno)){
		strcpy(dtmfinput.inroom, confno);
	} else {
	strcpy(dtmfinput.inroom, "");
	}

	strcpy(dtmfinput.inpass, "");

	/*
	if (!DBOpts) {
	    strcpy(dtmfmatch.roomtype, OptsUsr);
	}
	*/
	

	
	res = getConf(chan, dtmfinput, &dtmfmatch);
	ast_log(LOG_NOTICE, "getConf: %i\n", res);
	if (res == -1) {
		return -1;
	} else {
		if(res==1){
			res = getPass(chan, dtmfinput, &dtmfmatch);
			ast_log(LOG_NOTICE, "getPass: %i\n", res);
		} else {

			optLen = strlen(dtmfmatch.uFlags);
			for (pos = 0; pos < optLen; pos++){
				if ((dtmfmatch.uFlags[pos] != 'm') &&
				    (dtmfmatch.uFlags[pos] != 'w') &&
				    (dtmfmatch.uFlags[pos] != 'l') ) {
					dtmfmatch.roomtype[i++] = dtmfmatch.uFlags[pos];
					dtmfmatch.roomtype[i] = '\0';
				}
			}

			if(strchr(dtmfmatch.aFlags, 'r'))
				strcat(dtmfmatch.roomtype, "r");

			ast_log(LOG_NOTICE, "No User or Admin passwords\n");
			res = 1;
		}
	}
	if(res==1 || res==2 ){
		res = checkMax(chan, &dtmfmatch);
	ast_log(LOG_NOTICE, "checkMax: %i\n", res);}
	if(res==1){
		res = enterConf(chan, &dtmfmatch);
	ast_log(LOG_NOTICE, "enterConf: %i\n", res);}

	return res;
}

static char *complete_cmbysql_status(const char *line, const char *word, int pos, int state)
{
	 static char *cmds[] = {"status", NULL};

	if (pos == 1) 		/* Command */
		return ast_cli_complete(word, cmds, state);
}

static char *handle_cb_mysql_status(struct ast_cli_entry *e, int cmd, struct ast_cli_args *a)
{
	switch (cmd) {
	case CLI_INIT:
		e->command = "cbmysql";
		e->usage =
			"Usage:  cbmysql status\n"
			"	Shows current mysql connection status for CBMySQL\n";
	case CLI_GENERATE:
		return complete_cmbysql_status(a->line, a->word, a->pos, a->n);
	}

	if (connected) {
		char status[256], status2[100] = "";
		int ctime = time(NULL) - connect_time;
		if (dbport)
			snprintf(status, 255, "Connected to %s@%s, port %d", dbname, hostname, dbport);
		else if (dbsock)
			snprintf(status, 255, "Connected to %s on socket file %s", dbname, dbsock);
		else
			snprintf(status, 255, "Connected to %s@%s", dbname, hostname);

		if (dbuser && *dbuser)
			snprintf(status2, 99, " with username %s", dbuser);
		if (ctime > 31536000) {
			ast_cli(a->fd, "%s%s for %d years, %d days, %d hours, %d minutes, %d seconds.\n", status, status2, ctime / 31536000, (ctime % 31536000) / 86400, (ctime % 86400) / 3600, (ctime % 3600) / 60, ctime % 60);
		} else if (ctime > 86400) {
			ast_cli(a->fd, "%s%s for %d days, %d hours, %d minutes, %d seconds.\n", status, status2, ctime / 86400, (ctime % 86400) / 3600, (ctime % 3600) / 60, ctime % 60);
		} else if (ctime > 3600) {
			ast_cli(a->fd, "%s%s for %d hours, %d minutes, %d seconds.\n", status, status2, ctime / 3600, (ctime % 3600) / 60, ctime % 60);
		} else if (ctime > 60) {
			ast_cli(a->fd, "%s%s for %d minutes, %d seconds.\n", status, status2, ctime / 60, ctime % 60);
		} else {
			ast_cli(a->fd, "%s%s for %d seconds.\n", status, status2, ctime);
		}
		if (records == totalrecords)
			ast_cli(a->fd, "  Wrote %d records since last restart.\n", totalrecords);
		else
			ast_cli(a->fd, "  Wrote %d records since last restart and %d records since last reconnect.\n", totalrecords, records);
		return CLI_SUCCESS;
	} else {
		ast_cli(a->fd, "Not currently connected to a MySQL server.\n");
		return CLI_FAILURE;
	}
}

static struct ast_cli_entry cli_cbmysql[] = {
	AST_CLI_DEFINE( handle_cb_mysql_status, "Show connection status of CBMySQL"),
};

static int load_config(void)
{
	/* int res; */
	struct ast_config *cfg;
	struct ast_variable *var;
	const char *tmp;
	struct ast_flags config_flags = { 0 };

	cfg = ast_config_load(CBMYSQL_CONFIG, config_flags);
	if (!cfg) {
		ast_log(LOG_WARNING, "Unable to load config for CBMySQL: %s\n", CBMYSQL_CONFIG);
		return 0;
	}
	
	var = ast_variable_browse(cfg, "global");
	if (!var) {
		return 0;
	}

	tmp = ast_variable_retrieve(cfg,"global","hostname");
	if (tmp) {
		hostname = malloc(strlen(tmp) + 1);
		if (hostname != NULL) {
			hostname_alloc = 1;
			strcpy(hostname,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		hostname = malloc(strlen("localhost") + 1);
		if (hostname != NULL) {
			ast_log(LOG_WARNING,"MySQL server hostname not specified.  Assuming localhost\n");
			hostname_alloc = 1;
			strcpy(hostname,"localhost");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}

	tmp = ast_variable_retrieve(cfg,"global","dbname");
	if (tmp) {
		dbname = malloc(strlen(tmp) + 1);
		if (dbname != NULL) {
			dbname_alloc = 1;
			strcpy(dbname,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		dbname = malloc(strlen("meetme") + 1);
		if (dbname != NULL) {
			ast_log(LOG_WARNING,"MySQL database not specified.  Assuming meetme\n");
			dbname_alloc = 1;
			strcpy(dbname,"meetme");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}
	tmp = ast_variable_retrieve(cfg,"global","table");
	if (tmp) {
		table = malloc(strlen(tmp) + 1);
		if (table != NULL) {
			table_alloc = 1;
			strcpy(table,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		table = malloc(strlen("booking") + 1);
		if (table != NULL) {
			ast_log(LOG_WARNING,"MySQL table not specified.  Assuming booking\n");
			table_alloc = 1;
			strcpy(table,"booking");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}



	tmp = ast_variable_retrieve(cfg,"global","user");
	if (tmp) {
		dbuser = malloc(strlen(tmp) + 1);
		if (dbuser != NULL) {
			dbuser_alloc = 1;
			strcpy(dbuser,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		dbuser = malloc(strlen("root") + 1);
		if (dbuser != NULL) {
			ast_log(LOG_WARNING,"MySQL database user not specified.  Assuming root\n");
			strcpy(password,"root");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}

	tmp = ast_variable_retrieve(cfg,"global","sock");
	if (tmp) {
		dbsock = malloc(strlen(tmp) + 1);
		if (dbsock != NULL) {
			dbsock_alloc = 1;
			strcpy(dbsock,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		ast_log(LOG_WARNING,"MySQL database sock file not specified.  Using default\n");
		dbsock = NULL;
	}

	tmp = ast_variable_retrieve(cfg,"global","password");
	if (tmp) {
		password = malloc(strlen(tmp) + 1);
		if (password != NULL) {
			password_alloc = 1;
			strcpy(password,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
		password = malloc(strlen("") + 1);
		if (password != NULL) {
			ast_log(LOG_WARNING,"MySQL database password not specified.  Assuming blank\n");
			password_alloc = 1;
			strcpy(password,"");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}

	tmp = ast_variable_retrieve(cfg,"global","port");
	if (tmp) {
		if (sscanf(tmp,"%d",&dbport) < 1) {
			ast_log(LOG_WARNING,"Invalid MySQL port number.  Using default\n");
			dbport = 0;
		}
	}

        tmp = ast_variable_retrieve(cfg,"global","DBOpts");
        if (tmp) {
		DBOpts = ast_true(tmp);
	}

	if (!DBOpts){
	   tmp = ast_variable_retrieve(cfg,"global","OptsAdm");
	   if (tmp) {
		OptsAdm = malloc(strlen(tmp) + 1);
                if (OptsAdm != NULL) {
                        strcpy(OptsAdm,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	    } else {
		ast_log(LOG_WARNING,"Invalid Conference Admin options.  Using default\n");
	    }

      	   tmp = ast_variable_retrieve(cfg,"global","OptsUsr");
	   if (tmp) {
                   OptsUsr = malloc(strlen(tmp) + 1);
                   if (OptsUsr != NULL) {
                           strcpy(OptsUsr,tmp);
		   } else {
		      	   ast_log(LOG_ERROR,"Out of memory error.\n");
			   return -1;
		   }
	    } else {
		ast_log(LOG_WARNING,"Invalid Conference User options.  Using default\n");
	    }
}

	tmp = ast_variable_retrieve(cfg,"global","ConfApp");
	if (tmp) {
                ConfApp = malloc(strlen(tmp) + 1);
                if (ConfApp != NULL) {
                        strcpy(ConfApp,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
                ConfApp = malloc(strlen("MeetMe") + 1);
                if (ConfApp!= NULL) {
			ast_log(LOG_WARNING,"No Conference application.  Using MeetMe\n");
			strcpy(ConfApp, "MeetMe");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}

	tmp = ast_variable_retrieve(cfg,"global","ConfAppCount");
	if (tmp) {
                ConfAppCount = malloc(strlen(tmp) + 1);
                if (ConfAppCount!= NULL) {
                        strcpy(ConfAppCount,tmp);
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	} else {
                ConfAppCount = malloc(strlen("MeetMeCount") + 1);
                if (ConfAppCount!= NULL) {
			ast_log(LOG_WARNING,"No Conference count application.  Using MeetMe\n");
			strcpy(ConfAppCount, "MeetMeCount");
		} else {
			ast_log(LOG_ERROR,"Out of memory error.\n");
			return -1;
		}
	}

        tmp = ast_variable_retrieve(cfg,"global","earlyalert");
        if (tmp) {
                if (sscanf(tmp,"%d",&earlyalert) < 1) 
                        ast_log(LOG_WARNING,"Invalid Early Alert time.\n");
        }

        tmp = ast_variable_retrieve(cfg,"global","fuzzystart");
        if (tmp) {
                if (sscanf(tmp,"%d",&fuzzystart) < 1) 
                        ast_log(LOG_WARNING,"Invalid Fuzzy Start time.\n");
        }


	ast_config_destroy(cfg);

	if (option_debug > 3){
		ast_log(LOG_DEBUG,"CBMySQL: got hostname of %s\n",hostname);
		ast_log(LOG_DEBUG,"CBMySQL: got port of %d\n",dbport);
		if (dbsock)
			ast_log(LOG_DEBUG,"CBMySQL: got sock file of %s\n",dbsock);
		ast_log(LOG_DEBUG,"CBMySQL: got user of %s\n",dbuser);
		ast_log(LOG_DEBUG,"CBMySQL: got dbname of %s\n",dbname);
		ast_log(LOG_DEBUG,"CBMySQL: got password of %s\n",password);
		if (DBOpts) {
		     	ast_log(LOG_DEBUG,"CBMySQL: Using Database  for Admin & User Options\n ");
		} else {
	     		ast_log(LOG_DEBUG,"CBMySQL: got Admin Options of %s\n",OptsAdm);
	     		ast_log(LOG_DEBUG,"CBMySQL: got User Options of %s\n",OptsUsr);
		}
		ast_log(LOG_DEBUG,"CBMySQL: got Connference Application of %s\n",ConfApp);
		ast_log(LOG_DEBUG,"CBMySQL: got Conference Count Application of %s\n",ConfAppCount);
		if (earlyalert)
			ast_log(LOG_DEBUG, "CBMySQL: Early Alert set to %i seconds.\n", earlyalert);
		if (fuzzystart)
			ast_log(LOG_DEBUG, "CBMySQL: Fuzzy Start set to %i seconds.\n", fuzzystart);
	}

	ast_mutex_lock(&handle.lock);
	mysql_init(&handle.mysql);

	if (!mysql_real_connect(&handle.mysql, hostname, dbuser, password, dbname, dbport, dbsock, 0)) {
		ast_log(LOG_ERROR, "Failed to connect to mysql database %s on %s.\n", dbname, hostname);
		connected = 0;
		records = 0;
	} else {
		ast_log(LOG_NOTICE,"Successfully connected to MySQL database.\n");
		connected = 1;
		records = 0;
		connect_time = time(NULL);
	}
	ast_mutex_unlock(&handle.lock);
	if (connected)
		return 1;
	else
		return 0;
}

static int unload_module(void)
{
	int res;

	res = ast_cli_unregister_multiple(cli_cbmysql, ARRAY_LEN(cli_cbmysql));
	res |= ast_unregister_application(app);

	ast_module_user_hangup_all();

	return res;
}

static int load_module(void)
{
	int res;
	
	if (!load_config())
		return AST_MODULE_LOAD_DECLINE;

	res = ast_cli_register_multiple(cli_cbmysql, ARRAY_LEN(cli_cbmysql));
	res |= ast_register_application(app, cb_exec, synopsis, tdesc);
	
	return res;
}

static int reload(void)
{
	load_config();

	return 0;
}

AST_MODULE_INFO(ASTERISK_GPL_KEY, AST_MODFLAG_DEFAULT, "CBMysql conference scheduling",
                .load = load_module,
                .unload = unload_module,
                .reload = reload,
               );


