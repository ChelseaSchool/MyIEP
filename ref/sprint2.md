# Backlog: Spint 2

## UI/UX Overhaul

### Datepicker

For all date input fields, use datepicker function from include/ to make entering dates easier.

1. Add_goal_1.php 

2. add guardian

3. assistive tech

4. Bug report

5. coding.php

6. Coordination of services

7. duplicate

8. edit achieve level

9. edit bug

10. edit coordination of services

11. edit general

12. edit medical info

13. edit medication 

14. edit school history (low priority)

15. edit school

16. edit strength need

17. edit testing to support

18. edit transition plan

19. modify IPP-Permission

20. New IPP Permission

21. New Student

22. program area

23. student archive

24. superuser add program area

25. Superuser manage coding (low priority)

26. Superuser manage user

27. superuser manage users

28. superuser new member_2

29. superuser new member

30. Testing to support code

31. Transition Plan

32. User Audit

### Bootstrap Theme

For all student context pages, add bootstrap navbar from function in include/.

For all student context pages, add "jumbotron" and supporting files.

Scrap logo and tables from legacy interface. Revise forms using Bootstrap CSS specs.

Remove legacy navbar.

Remove/replace legacy datepicker

1. **edit_achieve_level.php**  (priority)

2. **edit_assistive_technology.php**  (priority)

3. **edit_coordination_of_services.php**  (priority)

4. edit_medical_info.php

5. edit_medication.php

6. **edit_strength_need.php**  (priority)

7. edit_support_member.php

8. **edit_transistion_plan.php**  (priority)

9. guardian_notes.php

10. medical_info.php

11. Medicaiton_view.php

12. New_student.php

13. **short_term_objectives.php**  (priority)

14. **strength_need_view.php**  (priority)

15. **transition_plan.php**  (priority)

## System Integrity, Security, Quality Control

1. Investigate password encryption

	e.g. salt+md5::

		<?php

		$salt = 'some_random_string';
		
		$password_hash = md5($salt . md5($_POST['password'] . $salt));

		?>


2. Force change password
  

3. Refactor (use Eclipse)

	* Revise Standards

	* Rename functions
 	
	* Rename files

	* Rename variables

	* Create functions for repeated procedures/code blocks (e.g. validation, permission check

4. Error traceback test; log errors with content

5. Complete logout: complete cleanup of session (timeout)

## HTML5

1. Validation

2. Form input validation

3. Form autofocus

4. Required fields

## Bug Fixes

Accomodations, Etc

1. Return functionality

	* Delete an entry
	* Mark entry complete, incomplete






