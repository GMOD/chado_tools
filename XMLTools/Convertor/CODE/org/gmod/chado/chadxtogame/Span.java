//Span
package org.gmod.chado.chadxtogame;

import java.util.*;

public class Span {
private int m_start=0;
private int m_end=0;
private String m_src;

	public Span(String the_start,String the_end){
		try{
			m_start = Integer.decode(the_start).intValue();
			m_end = Integer.decode(the_end).intValue();
		}catch(Exception ex){
			System.out.println("ERROR FOR SPAN STR <"+the_start+"><"+the_end+">");
			ex.printStackTrace();
		}
	}

	public Span(String the_start,String the_end,String the_src){
		this(the_start,the_end);
		m_src = the_src;
	}

	public Span(int the_start,int the_end){
		m_start = the_start;
		m_end = the_end;
	}

	public int getStart(){
		return m_start;
	}

	public int getEnd(){
		return m_end;
	}

	public String getSrc(){
		return m_src;
	}

	public int getLength(){
		if(m_start<m_end){
			return (m_end-m_start+1);
		}else{
			return (m_start-m_end+1);
		}
	}

	public boolean isForward(){
		if(m_end>m_start){
			return true;
		}else{
			return false;
		}
	}

	public String toString(){
		return (m_start+".."+m_end);
	}

	public Span advance(int the_amt){
		return (new Span((m_start+the_amt-1),(m_end+the_amt-1)));
	}

	public Span retreat(int the_amt){
		return (new Span((m_start-the_amt+1),(m_end-the_amt+1)));
	}

	public void Display(int the_depth){
		String offsetTxt = "";
		for(int i=0;i<the_depth;i++){
			offsetTxt += "\t";
		}
		System.out.println(offsetTxt+" Span <"+toString()+">");
	}
}

