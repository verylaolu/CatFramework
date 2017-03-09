/**
* Copyright (c) 2011
* All rights reserved.
* 
* Program Name:cToPhpForMcc.c
* summary:	getting log from mcc and send statPhp do with
* author: 	ihero
*/

#include <string>
#include <string.h>
#include <cerrno>
#include <unistd.h>
#include <sys/socket.h>
#include <netdb.h>
#include <sys/stat.h>
#include <stdarg.h>
#include <signal.h> 
#include <sys/types.h>
#include <sys/wait.h>
#include <stdio.h>
#include <stdlib.h>

using namespace std;

#define LOG_PATH_ERR    "runphpErr.log"
#define LOG_PATH_DEBUG    "runphpDebug.log"


void fetchtime(char *logDate);
void fetchData(char *logDate);
void write_log(const char *logName, const char *fmt, ...);

void msg_pipe_fork_exec(const string &filename);



const int MAX_CHILD_COUNT = 9;
volatile int child_count = 0;


void child_exit(int sig) {
  while (waitpid(WAIT_ANY, NULL, WNOHANG) != 0) {
    --child_count;
    if (child_count == 0) {
      return;
    }
  }
}


//#############################################################
//# function:
//#     get date and time
//# argument:
//#     logDate -- value of date and time 
//# return:
//#     NUll or void
//#############################################################
void fetchtime(char *logDate)
{
	time_t tNow = time(NULL);
	struct tm *ptmNow = localtime(&tNow);
	sprintf(logDate, "[%4d%02d%02d %02d%02d%02d] ", 
                                ptmNow->tm_year+1900, ptmNow->tm_mon+1, ptmNow->tm_mday, 
                                ptmNow->tm_hour, ptmNow->tm_min, ptmNow->tm_sec);
}

void fetchData(char *logDate)
{
	time_t tNow = time(NULL);
	struct tm *ptmNow = localtime(&tNow);
	sprintf(logDate, "%4d%02d%02d", ptmNow->tm_year+1900, ptmNow->tm_mon+1, ptmNow->tm_mday);
}

//#############################################################
//# function:
//#     write debug or record log
//# argument:
//#     1:logName: log file of writing
//#	2:fmt:	   format parameter like printf's argument
//# return:
//#     NUll or void
//#############################################################
void write_log(const char *logName, const char *fmt, ...)
{
        FILE *fpLog;
        char logTime[63] = {0};

        if (!logName || !fmt || !*logName || !*fmt)
        {
                return;
        }

        fpLog = fopen(logName, "ab");
        if (fpLog)
        {
                va_list args;
		fetchtime(logTime);
                fprintf(fpLog, "%s ", logTime);

                va_start(args, fmt);
                vfprintf(fpLog, fmt, args);
                va_end(args);
                fprintf(fpLog, "\n");
                fclose(fpLog);
        }
}


void msg_pipe_fork_exec(const string &filename)
{
	int p[2];
	pid_t pid;

	if (pipe(p) != 0) 
	{
		write_log(LOG_PATH_ERR, "Error openning pipe\n");
		return;
	}

	if (fork() == 0) 
	{
		pid = getpid();
		write_log(LOG_PATH_DEBUG, "fock id = %d, filename size = %s\n", pid, filename.c_str());
		close(p[1]);
		dup2(p[0], STDIN_FILENO);
		execlp("php", "php", filename.c_str(), NULL);
	}
	close(p[0]);
}

void signal_hook(int sig) 
{
	if (sig == SIGSEGV) 
	{
		write_log(LOG_PATH_ERR, "%s\tSIGSEGV caught!! set a breakpoint here and examine the call stack to debug\n", strerror(errno));
	}
}

int main(int argc, char *argv[]) 
{	
	int cnn = 0;
	string filename;
	int number = 0;
	time_t tNow;

  signal(SIGCHLD,SIG_IGN);
  signal(SIGPIPE,SIG_IGN);
	signal(SIGCHLD,child_exit);
  signal(SIGSEGV, signal_hook);
  
  if(argv[1] == NULL) 
	{ 
		fprintf(stderr,"usage:%s php filename\n",argv[0]);
		write_log(LOG_PATH_ERR, "usage:%s php filename\n",argv[0]);
		return 0;
	}
	filename = argv[1];
	
	tNow = time(NULL);
	while(1)
	{
		
		sleep(13);
		if (child_count > MAX_CHILD_COUNT) 
		{
			usleep(333333);
			continue;
		}
		
		if ((time(NULL)-tNow) > 53) 
		{
			msg_pipe_fork_exec(filename);
			tNow = time(NULL);
			++child_count;
		}
	}
	return 0;
}

