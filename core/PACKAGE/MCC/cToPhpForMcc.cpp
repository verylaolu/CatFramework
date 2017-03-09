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

#define LOG_PATH_ERR    "cToPhpForMccErr.log"
#define LOG_PATH_DEBUG    "cToPhpForMccDebug.log"
#define LOG_PATH_MSG	"cToPhpForMccMsg.log"

int portnumber;
struct hostent *hostname;
int sockfd = 0;

void fetchtime(char *logDate);
void fetchData(char *logDate);
void write_log(const char *logName, const char *fmt, ...);
int connect_server();
string receive_string(const int fd) ;
void msg_pipe_fork_exec(const string &msg, const string &filename);



const int MAX_CHILD_COUNT = 3;
volatile int child_count = 0;
volatile int MSG_COUNT = 1;

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

//#############################################################
//# function:
//#     connect mcc server
//# argument:
//#     NULL
//# return:
//#     1:30 is socket init is error
//#	2:31 is connect error
//#	3:0 is ok
//#############################################################
int connect_server()
{
	struct sockaddr_in server_addr;
	bzero(&server_addr,sizeof(server_addr)); 
	server_addr.sin_family=AF_INET; 
	server_addr.sin_port=htons(portnumber); 
	server_addr.sin_addr=*((struct in_addr *)hostname->h_addr); 
	
	if((sockfd=socket(AF_INET,SOCK_STREAM,0))==-1) 
	{
		return 30;
	}
	if(connect(sockfd,(struct sockaddr *)(&server_addr),sizeof(struct sockaddr))==-1) 
	{
		return 31;
	}
	return 0;
}

string receive_string(const int fd) {
  string result;
  int cnn = 0;
  char ch;
  for (;;) {
	if (recv(fd, &ch, 1, 0) <= 0)
	{
		sleep(1*60);
		cnn = connect_server();
		write_log(LOG_PATH_ERR, "%d\t%d\t%s\treceive string is error or connect mcc is error\n", cnn, errno, strerror(errno));
		result = "connect mcc is error\n";
		break;
	}
    result.push_back(ch);
    if (result.size() >= 9 && result.substr(result.size()-9, 9) == "</xcpmsg>") {
      break;
    } else if (result.size() >= 6 && result.substr(result.size()-6, 6) == "</xcp>") {
      break;
    } else if (result.size() >= 8 && result.substr(result.size()-8, 8) == "</error>") {
      break;
    }
  }
	//result.push_back('\n');
  return result;
}

void msg_pipe_fork_exec(const string &msg, const string &filename)
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
		write_log(LOG_PATH_DEBUG, "fock id = %d,  msg size %d\n", pid, msg.size());
		close(p[1]);
		dup2(p[0], STDIN_FILENO);
		execlp("php", "php", filename.c_str(), NULL);
	}
	close(p[0]);

	FILE *ps = fdopen(p[1], "w");
	fprintf (ps, "%s", msg.c_str());
	fclose (ps);


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
	char buffer[1024] = {0};
	string filename;

        signal(SIGCHLD,SIG_IGN);
        signal(SIGPIPE,SIG_IGN);
	signal(SIGCHLD,child_exit);
        signal(SIGSEGV, signal_hook);
	if (argc != 4)
	{
		fprintf(stderr,"usage:%s hostName portNumber phpfile\n",argv[0]);
		write_log(LOG_PATH_ERR, "usage:%s hostName portNumber phpfile\n",argv[0]);
		return 0;
	}
	if(argv[1] == NULL || (hostname=gethostbyname(argv[1]))==NULL) 
	{ 
		fprintf(stderr,"do not Get socket server hostname error\n"); 
		write_log(LOG_PATH_ERR, "do not Get socket server hostname error\n"); 
		return 3;
	}
	if(argv[2] == NULL || (portnumber=atoi(argv[2]))<0) 
	{
		fprintf(stderr,"do not Get port error\n"); 
		write_log(LOG_PATH_ERR, "do not Get port error\n"); 
		return 4; 
	}
	
	if(argv[3] == NULL) 
	{ 
		fprintf(stderr,"do not Get  php filename\n");
		write_log(LOG_PATH_ERR, "do not Get  php filename\n");
		return 0;
	}
	filename = argv[3];
	
	//connect mcc server
	cnn = connect_server();
	if (cnn != 0)
	{
		fprintf(stderr,"socket connect error %d\n", cnn); 
		write_log(LOG_PATH_ERR, "socket connect error %d\n", cnn); 
		return 5; 
	}
	memset(buffer,0,sizeof(buffer));

	sprintf(buffer, "<xcpmsg action=\"rec\" dest=\"messageChannel\"></xcpmsg>");
	while(1)
	{
		sleep(1);
		if (sockfd <= 0)
		{
			sleep(1*60);
			cnn = connect_server();
			write_log(LOG_PATH_ERR, "\t send string is error or mcc server is error:%d\n", cnn); 
			continue;
		}
		usleep(3);
		if (child_count > MAX_CHILD_COUNT) 
		{
			usleep(333333);
			continue;
		}

		if (send(sockfd, buffer, strlen(buffer), 0) <= 0)
		{
			sleep(3);
			write_log(LOG_PATH_ERR, "000error:%s\n", strerror(errno)); 
			shutdown(sockfd, 2);
			close(sockfd);
			sockfd = 0;
			continue;
		}
		if (errno ==EPIPE)
		{
			sleep(3);
			write_log(LOG_PATH_ERR, "111error:%s\n", strerror(errno)); 
			shutdown(sockfd, 2);
			close(sockfd);
			sockfd = 0;
			continue;
		}
		string s = receive_string(sockfd);
		if (s.size() <= MSG_COUNT)
		{
			sleep(3);
			continue;
		}
		//replace(s.begin(), s.end(), '\r', ' ');
		//replace(s.begin(), s.end(), '\n', ' ');
		write_log(LOG_PATH_MSG, "msg: %s\n", s.c_str());
		s.push_back('\n');
		msg_pipe_fork_exec(s,filename);
		s.clear();
		++child_count;
	}
	
	shutdown(sockfd, 2);
	close(sockfd);
	return 0;
}

