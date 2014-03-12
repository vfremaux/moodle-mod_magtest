MAGTEST Moodle 2 release
########################

magtest implements a online test based on magazine self testing, using a set of 
answers tagged with letters A,B,C,D,... or symbols. User answers are counted "by answer range",
and a test feedback is given depending on each answer serie count balance. 

Developers : Valery Fremaux, Etienne Roze
Moodle 2 version : Valery Fremaux, Wafa Adham

Contact valery.fremaux@gmail.com

Features available : 

- Setup categories and optionally associate customisable graphical icon for them 
- Define questions
- Define choice otpions for the questions associated with each category
- Define conclusions per category that will displayes as debriefing when the test gets finished.
- Optionnaly weight answers (an aswer may not add always the same amount of "points" to a category score).
- Preview the test with weighting (according to capability "mod/magtest:manage")
- Play the test (according to capability "mod/magtest:doit")
- View the test result when finished
- Replay the test if allowed (according to capability "mod/magtest:multipleattempts" plus a global "per instance" feature switch).
- View the complete results (according to capability "mod/magtest:viewotherresults")
     - Per user
     - Per category with additional hint : 
     convert test results into Moodle groups if: 
          - course has no group set
          - The "per instance" feature switch "usemakegroups" is enabled.

- View global stats about answers. 
- Display a global conclusion
- Backup module
- Restore
- Do not allow playing the test if current time is former than an enabled starttime.
- Do not allow play nor replay if current time is over an enabled endtime
- Delete all user's answers on course reset

